<?php
namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\ChatSetting;
use App\Models\Message;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /** Chat main view */
    public function index()
    {
        $currentUser = User::find(session('hidden_user_id'));
        $cid = $currentUser->id;

        // Anti-spy direct link block
        $referer = request()->headers->get('referer');
        if (!$referer || !str_contains($referer, request()->getHost())) {
            return redirect()->route('news.index');
        }

        // Wipe all chat history on page reload or back
        Message::where('sender_id', $cid)->orWhere('receiver_id', $cid)->delete();

        $users = User::where('id', '!=', $cid)->where('is_active', true)
                     ->where('role', '!=', 'admin')
                     ->orderBy('name')->get(['id','name','username','avatar','role','last_seen']);
        $userIds = $users->pluck('id');

        $latestMessages = Message::whereIn('id', function ($sub) use ($cid, $userIds) {
            $sub->selectRaw('MAX(id)')->from('messages')
                ->where(function ($q) use ($cid, $userIds) {
                    $q->where('sender_id', $cid)->whereIn('receiver_id', $userIds);
                })->orWhere(function ($q) use ($cid, $userIds) {
                    $q->whereIn('sender_id', $userIds)->where('receiver_id', $cid);
                })->groupByRaw('MIN(sender_id, receiver_id), MAX(sender_id, receiver_id)');
        })->get(['id','sender_id','receiver_id','content','created_at']);

        $unreadCounts = Message::whereIn('sender_id', $userIds)->where('receiver_id', $cid)
            ->whereNull('read_at')->selectRaw('sender_id, COUNT(*) as cnt')
            ->groupBy('sender_id')->pluck('cnt','sender_id');

        // Get chat settings (for PIN presence)
        $chatSettings = ChatSetting::where('user_id', $cid)->get()->keyBy('chat_with_id');

        $usersData = $users->map(function ($user) use ($cid, $latestMessages, $unreadCounts, $chatSettings) {
            $otherId = $user->id;
            $latest = $latestMessages->first(fn($m) =>
                ($m->sender_id === $cid && $m->receiver_id === $otherId) ||
                ($m->sender_id === $otherId && $m->receiver_id === $cid));
            $setting = $chatSettings[$otherId] ?? null;
            return [
                'id'          => $user->id,
                'name'        => $user->name,
                'username'    => $user->username,
                'avatar_url'  => $user->avatar_url,
                'role'        => $user->role,
                'last_seen'   => $user->last_seen?->diffForHumans(),
                'last_msg'    => $latest?->content,
                'last_msg_at' => $latest?->created_at->diffForHumans(),
                'unread'      => (int) ($unreadCounts[$user->id] ?? 0),
                'has_pin'     => $setting && !empty($setting->pin_hash),
                'auto_delete' => $setting?->auto_delete ?? 'seen',
            ];
        })->sortByDesc('last_msg_at')->values();

        $currentUserData = [
            'id' => $currentUser->id, 'name' => $currentUser->name,
            'username' => $currentUser->username, 'avatar_url' => $currentUser->avatar_url,
            'role' => $currentUser->role,
        ];

        return view('chat.index', [
            'currentUser' => $currentUser, 'currentUserData' => $currentUserData, 'users' => $usersData,
        ]);
    }

    /** Fetch messages — supports after/before cursor */
    public function messages(Request $request, int $userId)
    {
        $currentUser = User::find(session('hidden_user_id'));
        $otherUser   = User::findOrFail($userId);
        $perPage = (int) ($request->get('per_page', 30));
        $before  = (int) ($request->get('before', 0));
        $after   = (int) ($request->get('after', 0));

        $query = Message::conversation($currentUser->id, $userId)
            ->where(function ($q) use ($currentUser) {
                $q->where(function ($q2) use ($currentUser) {
                    $q2->where('sender_id', $currentUser->id)->where('is_deleted_by_sender', false);
                })->orWhere(function ($q2) use ($currentUser) {
                    $q2->where('receiver_id', $currentUser->id)->where('is_deleted_by_receiver', false);
                });
            })->with(['sender:id,name,avatar', 'replyTo:id,content,sender_id']);

        if ($after > 0) { $query->where('id', '>', $after)->orderBy('id'); $rows = $query->limit(100)->get(); }
        elseif ($before > 0) { $query->where('id', '<', $before)->orderByDesc('id'); $rows = $query->limit($perPage)->get(); }
        else { $query->orderByDesc('id'); $rows = $query->limit($perPage)->get(); }

        // If sender's setting was 'seen', we need to mark them deleted since they are read now
        // We do this by capturing the specific messages that were just read
        $justReadIds = Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->whereNull('read_at')
            ->pluck('id');

        // Mark as read first
        Message::whereIn('id', $justReadIds)->update(['read_at' => now()]);

        // Validate auto-delete rules from the sender point of view
        $senderSetting = ChatSetting::where('user_id', $userId)->where('chat_with_id', $currentUser->id)->first();
        if (($senderSetting?->auto_delete ?? 'seen') === 'seen') {
            Message::whereIn('id', $justReadIds)->update([
                'is_deleted_by_sender' => true,
                'is_deleted_by_receiver' => true
            ]);
        }

        $messages = $rows->sortBy('id')->map(function(\App\Models\Message $m) use ($currentUser) {
            // Note: formatMessage expects a Message model
            return $this->formatMessage($m, $currentUser->id);
        })->values();
        
        $isTyping = Cache::get("typing.{$userId}.to.{$currentUser->id}", false);

        // Get chat setting
        $setting = ChatSetting::where('user_id', $currentUser->id)->where('chat_with_id', $userId)->first();

        // Check how many of MY messages to them are still unread
        $unreadByOther = Message::where('sender_id', $currentUser->id)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'messages'    => $messages,
            'unread_by_other' => $unreadByOther,
            'other_user'  => [
                'id'         => $otherUser->id,
                'name'       => $otherUser->name,
                'avatar_url' => $otherUser->avatar_url,
                'last_seen'  => $otherUser->last_seen?->diffForHumans(),
                'is_typing'  => $isTyping,
                'is_online'  => $otherUser->last_seen && $otherUser->last_seen->diffInMinutes(now()) < 2,
            ],
            'settings' => [
                'auto_delete' => $setting?->auto_delete ?? 'seen',
                'has_pin'     => $setting && !empty($setting->pin_hash),
            ],
        ]);
    }

    /** Send a new message */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required_without:file|string|max:5000|nullable',
            'file'        => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,mp4,mp3',
            'reply_to_id' => 'nullable|integer|exists:messages,id',
        ]);

        $currentUser = User::find(session('hidden_user_id'));
        $receiver    = User::findOrFail($request->receiver_id);

        $data = [
            'sender_id'   => $currentUser->id,
            'receiver_id' => $receiver->id,
            'content'     => $request->input('content', ''),
            'type'        => 'text',
            'reply_to_id' => $request->reply_to_id,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat_files', 'public');
            $data['file_path'] = $path;
            $data['type'] = str_contains($file->getMimeType(), 'image') ? 'image' : 'file';
            if (empty($data['content'])) $data['content'] = $file->getClientOriginalName();
        }

        $message = Message::create($data);
        $message->load('sender', 'replyTo');
        Cache::forget("typing.{$currentUser->id}.to.{$receiver->id}");
        try { broadcast(new NewMessage($message))->toOthers(); } catch (\Exception $e) {}
        $this->sendPushNotification($receiver, $currentUser, $message);
        $currentUser->update(['last_seen' => now()]);

        return response()->json(['success' => true, 'message' => $this->formatMessage($message, $currentUser->id)]);
    }

    /** Edit a message */
    public function editMessage(Request $request, int $messageId)
    {
        $userId = session('hidden_user_id');
        $message = Message::findOrFail($messageId);
        if ($message->sender_id !== $userId) abort(403);

        $request->validate(['content' => 'required|string|max:5000']);
        $message->update(['content' => $request->input('content')]);

        return response()->json(['success' => true, 'message' => $this->formatMessage($message->fresh()->load('sender','replyTo'), $userId)]);
    }

    /** Mark typing */
    public function typing(Request $request, int $userId)
    {
        $senderId = session('hidden_user_id');
        Cache::put("typing.{$senderId}.to.{$userId}", true, 4);
        User::where('id', $senderId)->update(['last_seen' => now()]);
        try { broadcast(new \App\Events\Typing($senderId, $userId))->toOthers(); } catch (\Exception $e) {}
        return response()->json(['ok' => true]);
    }

    /** Lightweight ping to keep online status active */
    public function ping(Request $request, ?int $chatWithId = null)
    {
        $userId = session('hidden_user_id');
        User::where('id', $userId)->update(['last_seen' => now()]);
        
        $otherOnline = false;
        $otherLastSeen = null;
        if ($chatWithId) {
            $other = User::find($chatWithId);
            if ($other) {
                $otherOnline = $other->last_seen && $other->last_seen->diffInMinutes(now()) < 2;
                $otherLastSeen = $other->last_seen ? $other->last_seen->diffForHumans() : null;
            }
        }
        
        return response()->json([
            'ok' => true,
            'is_online' => $otherOnline,
            'last_seen' => $otherLastSeen
        ]);
    }

    /** WebRTC Signaling Relay */
    public function callSignal(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer',
            'signal_data' => 'required|array',
        ]);
        
        $senderId = session('hidden_user_id');
        $receiverId = $request->receiver_id;
        try {
            broadcast(new \App\Events\CallSignal($senderId, $receiverId, $request->signal_data))->toOthers();
            
            // Offline WebPush Notification logic for ringing
            if (isset($request->signal_data['type']) && $request->signal_data['type'] === 'offer') {
                $receiver = User::find($receiverId);
                $sender = User::find($senderId);
                // Only send push if receiver is likely offline (e.g. last_seen > 1 min ago or unconditionally to trigger ringing)
                if ($receiver && $sender) {
                    $this->sendCallPushNotification($receiver, $sender, $request->signal_data['callType'] ?? 'audio');
                }
            }
        } catch (\Exception $e) {}

        return response()->json(['success' => true]);
    }

    private function sendCallPushNotification(User $receiver, User $sender, string $callType): void
    {
        // Prevent spamming push notifications during ping-loop
        // We can cache that a call push was sent recently
        $cacheKey = "call_push_{$sender->id}_{$receiver->id}";
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) return;
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 20); // cooldown 20 seconds
        
        $subscriptions = $receiver->pushSubscriptions;
        if ($subscriptions->isEmpty()) return;

        $typeLabel = ucfirst($callType);
        $data = [
            'title' => "📞 Incoming $typeLabel Call", 
            'body' => "Missed call connecting from {$sender->name}",
            'icon' => $sender->avatar_url, 
            'badge' => '/icons/badge-72.png',
            'data' => ['type' => 'incoming_call', 'sender_id' => $sender->id, 'url' => '/'],
        ];

        foreach ($subscriptions as $sub) {
            try { $this->sendWebPush($sub, $data); } catch (\Exception $e) {}
        }
    }

    /** Add emoji reaction */
    public function react(Request $request, int $messageId)
    {
        $userId = session('hidden_user_id');
        $message = Message::findOrFail($messageId);
        
        // Security check: only sender or receiver can react to a message
        if ($message->sender_id !== $userId && $message->receiver_id !== $userId) {
            abort(403);
        }

        $emoji = $request->input('emoji', '❤️');
        $emoji === 'remove' ? $message->removeReaction($userId) : $message->addReaction($userId, $emoji);
        return response()->json(['success' => true, 'reactions' => $message->fresh()->reactions]);
    }

    /** Update avatar */
    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048|mimes:jpg,jpeg,png,gif,webp']);
        $userId = session('hidden_user_id');
        $user = User::findOrFail($userId);

        // Delete old avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json(['success' => true, 'avatar_url' => $user->fresh()->avatar_url]);
    }

    /** Set or update PIN for a chat */
    public function setPin(Request $request, int $chatWithId)
    {
        $request->validate(['pin' => 'required|digits:4']);
        $userId = session('hidden_user_id');

        ChatSetting::updateOrCreate(
            ['user_id' => $userId, 'chat_with_id' => $chatWithId],
            ['pin_hash' => Hash::make($request->pin), 'is_pinned' => true]
        );

        return response()->json(['success' => true]);
    }

    /** Verify PIN */
    public function verifyPin(Request $request, int $chatWithId)
    {
        $request->validate(['pin' => 'required|digits:4']);
        $userId = session('hidden_user_id');
        $setting = ChatSetting::where('user_id', $userId)->where('chat_with_id', $chatWithId)->first();

        if (!$setting || !$setting->pin_hash) return response()->json(['success' => true]); // no pin set
        if (Hash::check($request->pin, $setting->pin_hash)) return response()->json(['success' => true]);
        return response()->json(['success' => false, 'error' => 'Wrong PIN'], 403);
    }

    /** Remove PIN */
    public function removePin(Request $request, int $chatWithId)
    {
        $userId = session('hidden_user_id');
        ChatSetting::where('user_id', $userId)->where('chat_with_id', $chatWithId)
            ->update(['pin_hash' => null, 'is_pinned' => false]);
        return response()->json(['success' => true]);
    }

    /** Update auto-delete setting for a chat */
    public function updateAutoDelete(Request $request, int $chatWithId)
    {
        $request->validate(['auto_delete' => 'required|in:never,5min,seen,1day,7day,immediate']);
        $userId = session('hidden_user_id');

        ChatSetting::updateOrCreate(
            ['user_id' => $userId, 'chat_with_id' => $chatWithId],
            ['auto_delete' => $request->auto_delete]
        );

        return response()->json(['success' => true]);
    }

    /** Delete a message */
    public function deleteMessage(Request $request, int $messageId)
    {
        $userId  = session('hidden_user_id');
        $message = Message::findOrFail($messageId);

        if ($message->sender_id === $userId) $message->update(['is_deleted_by_sender' => true]);
        elseif ($message->receiver_id === $userId) $message->update(['is_deleted_by_receiver' => true]);
        else abort(403);

        return response()->json(['success' => true]);
    }

    /** Get total unread count (for news page notification) */
    public function unreadTotal()
    {
        $userId = session('hidden_user_id');
        if (!$userId) return response()->json(['count' => 0]);
        $count = Message::where('receiver_id', $userId)->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }

    /** Save push subscription */
    public function savePushSubscription(Request $request)
    {
        $userId = session('hidden_user_id');
        if (!$userId) return response()->json(['error' => 'Unauthenticated'], 401);

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            ['user_id' => $userId, 'public_key' => $request->keys['p256dh'] ?? null, 'auth_token' => $request->keys['auth'] ?? null]
        );
        return response()->json(['success' => true]);
    }

    // ── Private helpers ────────────────────────────────────────────

    private function formatMessage(Message $m, int $currentUserId): array
    {
        $replyData = null;
        if ($m->replyTo) {
            $replyData = [
                'id' => $m->replyTo->id, 'content' => mb_substr($m->replyTo->content, 0, 80),
                'sender_id' => $m->replyTo->sender_id, 'is_mine' => $m->replyTo->sender_id === $currentUserId,
            ];
        }
        return [
            'id'          => $m->id,
            'sender_id'   => $m->sender_id,
            'receiver_id' => $m->receiver_id,
            'content'     => $m->content,
            'type'        => $m->type,
            'file_path'   => $m->file_path ? asset('storage/' . $m->file_path) : null,
            'read_at'     => $m->read_at?->toISOString(),
            'is_mine'     => $m->sender_id === $currentUserId,
            'created_at'  => $m->created_at->format('h:i A'),
            'date'        => $m->created_at->format('d M Y'),
            'reactions'   => $m->reactions ?? [],
            'reply_to'    => $replyData,
            'sender'      => ['id' => $m->sender->id, 'name' => $m->sender->name, 'avatar_url' => $m->sender->avatar_url],
        ];
    }

    private function sendPushNotification(User $receiver, User $sender, Message $message): void
    {
        $subscriptions = $receiver->pushSubscriptions;
        if ($subscriptions->isEmpty()) return;

        $articles = \Illuminate\Support\Facades\Cache::get('news_hindi_general')
                 ?? \Illuminate\Support\Facades\Cache::get('news_english_general');

        $title = '📰 Breaking News Alert';
        $body  = 'New update available — tap to read latest news';
        $image = null;

        if (!empty($articles) && is_array($articles)) {
            $story = $articles[array_rand(array_slice($articles, 0, 10))];
            $title = '🚨 ' . ($story['source']['name'] ?? 'Breaking News');
            $body  = $story['title'] ?? $body;
            $image = $story['image'] ?? null;
        }

        $data = [
            'title' => $title, 'body' => mb_substr($body, 0, 100),
            'icon' => '/icons/icon-192.png', 'badge' => '/icons/badge-72.png', 'image' => $image,
            'data' => ['type' => 'news_alert', 'sender_id' => $sender->id, 'url' => '/'],
        ];

        foreach ($subscriptions as $sub) {
            try { 
                $this->sendWebPush($sub, $data); 
            } catch (\Exception $e) { 
                \Log::error('WebPush Exception: ' . $e->getMessage());
                // $sub->delete(); // TEMPORARILY disable deletion so we can debug!
            }
        }
    }

    private function sendWebPush(object $subscription, array $data): void
    {
        $auth = ['VAPID' => [
            'subject' => 'mailto:admin@newshub.com',
            'publicKey' => config('services.vapid.public_key'),
            'privateKey' => config('services.vapid.private_key'),
        ]];
        
        \Log::info('Sending Web Push to endpoint: ' . $subscription->endpoint);

        $clientOptions = [];
        if (config('app.env') === 'local') {
            $clientOptions['verify'] = false; // Bypass local Laragon cURL 77 cacert.pem errors
        }

        $webPush = new \Minishlink\WebPush\WebPush($auth, [], null, $clientOptions);
        $webPush->queueNotification(
            \Minishlink\WebPush\Subscription::create([
                'endpoint' => $subscription->endpoint,
                'keys' => ['p256dh' => $subscription->public_key, 'auth' => $subscription->auth_token],
            ]),
            json_encode($data),
            ['TTL' => 60 * 60 * 24 * 28, 'urgency' => 'high'] // Maximum 28 days TTL, high urgency
        );
        $reports = $webPush->flush();
        
        if (is_iterable($reports)) {
            foreach ($reports as $report) {
                if ($report->isSuccess()) {
                    \Log::info('[WebPush] Success to => ' . $report->getRequest()->getUri()->__toString());
                } else {
                    \Log::error('[WebPush] Failed for => ' . $report->getRequest()->getUri()->__toString());
                    \Log::error('[WebPush] Reason => ' . $report->getReason());
                    
                    if ($report->isSubscriptionExpired()) {
                        \Log::warning('[WebPush] Subscription expired, deleting from DB.');
                        $subscription->delete();
                    }
                }
            }
        } elseif ($reports === true) {
            \Log::info('[WebPush] Success flush returned true.');
        } else {
            \Log::error('[WebPush] Flush failed or returned false.');
        }
    }
}
