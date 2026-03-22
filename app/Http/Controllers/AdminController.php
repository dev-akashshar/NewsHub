<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /** Admin dashboard view */
    public function dashboard()
    {
        $stats = [
            'total_users'    => User::where('role', 'user')->count(),
            'total_admins'   => User::where('role', 'admin')->count(),
            'total_messages' => Message::count(),
            'active_users'   => User::where('last_seen', '>=', now()->subMinutes(5))->count(),
        ];

        $recentMessages = Message::with(['sender:id,name', 'receiver:id,name'])
            ->latest()
            ->limit(20)
            ->get();

        $users = User::orderByDesc('created_at')->get();

        $settings = [
            'logo_click_count' => Setting::get('logo_click_count', 7),
            'app_name'         => Setting::get('app_name', 'NewsHub'),
            'news_api_key'     => Setting::get('news_api_key', ''),
            'session_timeout'  => Setting::get('session_timeout', 30),
        ];

        return view('admin.dashboard', compact('stats', 'recentMessages', 'users', 'settings'));
    }

    // ── User Management ────────────────────────────────────────────

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'sometimes|string|max:100',
            'username'  => ['sometimes', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->ignore($id)],
            'password'  => 'sometimes|nullable|string|min:6',
            'role'      => 'sometimes|in:admin,user',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->only(['name', 'username', 'role', 'is_active']);

        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => true, 'user' => $user->fresh()]);
    }

    public function deleteUser(int $id)
    {
        $adminId = session('hidden_user_id');

        if ($id === $adminId) {
            return response()->json(['error' => 'Cannot delete yourself'], 422);
        }

        User::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function resetPassword(Request $request, int $id)
    {
        $request->validate(['password' => 'required|string|min:6']);

        User::findOrFail($id)->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['success' => true]);
    }

    public function toggleUser(int $id)
    {
        $user      = User::findOrFail($id);
        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        return response()->json(['success' => true, 'is_active' => $newStatus]);
    }

    // ── Settings ───────────────────────────────────────────────────

    public function updateSettings(Request $request)
    {
        $request->validate([
            'logo_click_count' => 'sometimes|integer|min:3|max:20',
            'app_name'         => 'sometimes|string|max:100',
            'news_api_key'     => 'sometimes|string|nullable',
            'session_timeout'  => 'sometimes|integer|min:1|max:1440',
        ]);

        foreach ($request->only(['logo_click_count', 'app_name', 'news_api_key', 'session_timeout']) as $key => $value) {
            Setting::set($key, $value);
        }

        // Clear news cache after API key change
        \Illuminate\Support\Facades\Cache::flush();

        return response()->json(['success' => true]);
    }

    // ── Message Monitoring ─────────────────────────────────────────

    public function messages(Request $request)
    {
        $userId = $request->get('user_id');

        $query = Message::with(['sender:id,name', 'receiver:id,name'])->latest();

        if ($userId) {
            $query->where(fn($q) =>
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId)
            );
        }

        return response()->json($query->paginate(50));
    }

    public function deleteMessage(int $id)
    {
        Message::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
