<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#030712">
    <title>NewsHub — Breaking News & Headlines</title>
    <link rel="manifest" href="/manifest.json">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']},colors:{brand:{500:'#f43f5e',600:'#e11d48'}}}}}</script>
    <style>
        *{font-family:'Inter',system-ui,sans-serif}
        body{-webkit-user-select:none;user-select:none}
        .msg-text{-webkit-user-select:text;user-select:text}
        @media print{body{display:none!important}}
        .chat-bg{background:linear-gradient(180deg,#030712 0%,#0a0f1a 100%)}
        .msg-bubble{max-width:min(85%,420px)}
        .scrollbar-thin::-webkit-scrollbar{width:3px}
        .scrollbar-thin::-webkit-scrollbar-thumb{background:#1e293b;border-radius:4px}
        #chat-messages{scroll-behavior:smooth}
        .user-item.active{background:linear-gradient(135deg,rgba(244,63,94,.1),rgba(99,102,241,.1));border-left-color:#f43f5e}
        .glass{background:rgba(10,15,26,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
        @keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}.msg-anim{animation:fadeIn .2s ease-out}
        @keyframes slideR{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}.slide-in{animation:slideR .3s ease-out both}
        .ctx-menu{position:fixed;z-index:100;background:#1e293b;border:1px solid rgba(51,65,85,.6);border-radius:14px;padding:5px;min-width:170px;box-shadow:0 8px 24px rgba(0,0,0,.5);animation:fadeIn .12s ease-out}
        .ctx-item{display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:10px;font-size:13px;color:#e2e8f0;cursor:pointer;transition:all .15s}
        .ctx-item:hover{background:rgba(255,255,255,.08)}.ctx-item.danger{color:#f87171}.ctx-item.danger:hover{background:rgba(239,68,68,.15)}
        .reply-preview{background:rgba(99,102,241,.12);border-left:3px solid #6366f1;border-radius:0 8px 8px 0;padding:4px 10px;margin-bottom:4px}
        @keyframes typB{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-4px)}}.typB{animation:typB .6s infinite;display:inline-block}.typB:nth-child(2){animation-delay:.1s}.typB:nth-child(3){animation-delay:.2s}
        @keyframes onP{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.4)}50%{box-shadow:0 0 0 4px rgba(16,185,129,0)}}.onP{animation:onP 2s infinite}
        .modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);z-index:60;display:none;align-items:center;justify-content:center;padding:16px}
        .modal-bg.show{display:flex}
        .pin-dot{width:12px;height:12px;border-radius:50%;border:2px solid #475569;transition:all .2s}.pin-dot.filled{background:#f43f5e;border-color:#f43f5e}
        /* Top Class Privacy Screen Overlay */
        #privacy-overlay { position:fixed; inset:0; background:#000; z-index:999999; display:none; flex-direction:column; justify-content:center; align-items:center; color:white; }
        body.privacy-active #privacy-overlay { display:flex; }
        body.privacy-active .flex { filter:blur(20px); pointer-events:none; }
    </style>
</head>
<body class="bg-[#030712] text-slate-100 h-screen flex flex-col overflow-hidden">
<div id="privacy-overlay">
    <span class="text-4xl mb-4">🛡️</span>
    <h2 class="text-xl font-bold font-sans tracking-wide text-brand-500">Secure Mode Active</h2>
    <p class="text-slate-400 mt-2 text-sm text-center px-4">Screen recording prevention triggered.<br>Tap or click to resume.</p>
</div>
<script>
window.APP={
    csrfToken:document.querySelector('meta[name="csrf-token"]').content,
    currentUser:@json($currentUserData),
    routes:{
        logout:'{{ route("hidden.logout") }}',sessChk:'{{ route("hidden.session-check") }}',
        messages:'/chat/messages/',send:'{{ route("chat.send") }}',editMsg:'/chat/message/',
        typing:'/chat/typing/',react:'/chat/react/',delMsg:'/chat/message/',
        avatar:'{{ route("chat.avatar") }}',
        setPin:'/chat/pin/',verifyPin:'/chat/pin/',removePin:'/chat/pin/',
        autoDelete:'/chat/auto-delete/',
        pushSub:'{{ route("chat.push-subscription") }}',news:'{{ route("news.index") }}',
        admin:'{{ route("admin.dashboard") }}',
    },
    vapidKey:'{{ config("services.vapid.public_key") }}',
};
</script>
<div class="flex h-full overflow-hidden">
<!-- SIDEBAR -->
<aside id="sidebar" class="w-full sm:w-80 lg:w-[22rem] flex-shrink-0 bg-[#0a0f1a] border-r border-slate-800/40 flex flex-col sm:translate-x-0 transition-transform duration-300 z-30 fixed sm:relative inset-y-0 left-0">
    <div class="flex items-center justify-between px-4 h-[56px] border-b border-slate-800/40 flex-shrink-0">
        <div class="flex items-center gap-3 cursor-pointer" id="avatar-area">
            <div class="relative"><img src="{{ $currentUser->avatar_url }}" class="w-9 h-9 rounded-2xl object-cover ring-2 ring-brand-500/20" alt="" id="my-avatar"><span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-[#0a0f1a] onP"></span></div>
            <div><p class="text-white text-sm font-bold">{{ $currentUser->name }}</p><p class="text-[10px] font-semibold bg-gradient-to-r from-brand-500 to-violet-400 bg-clip-text text-transparent">{{ $currentUser->role==='admin'?'👑 Admin':'🔒 Secure' }}</p></div>
        </div>
        <div class="flex items-center gap-1">
            <button id="mute-btn" class="w-7 h-7 rounded-lg bg-slate-800/50 hover:bg-slate-700 text-slate-500 hover:text-amber-400 flex items-center justify-center transition text-sm" title="Notifications">🔔</button>
            @if($currentUser->role==='admin')<a href="{{ route('admin.dashboard') }}" class="w-7 h-7 rounded-lg bg-slate-800/50 hover:bg-slate-700 text-slate-500 hover:text-indigo-400 flex items-center justify-center transition" title="Admin"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>@endif
            <button id="exit-btn" class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition" title="Exit"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button>
        </div>
    </div>
    <div class="px-4 py-1.5 border-b border-slate-800/30 flex items-center gap-1.5 text-emerald-500"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg><span class="text-[10px] font-bold uppercase tracking-widest">E2E Secure</span></div>
    <div class="px-3 py-2"><div class="relative"><svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input id="user-search" type="text" placeholder="Search…" class="w-full bg-slate-900/50 border border-slate-800/40 text-sm text-white placeholder-slate-600 rounded-xl pl-9 pr-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-500/40 transition"></div></div>
    <div id="user-list" class="flex-1 overflow-y-auto scrollbar-thin px-1.5 space-y-px">
        @foreach($users as $u)
        <button class="user-item w-full flex items-center gap-2.5 px-2.5 py-3 rounded-xl transition-all text-left border-l-2 border-transparent slide-in" data-user-id="{{ $u['id'] }}" data-name="{{ $u['name'] }}" data-avatar="{{ $u['avatar_url'] }}" data-has-pin="{{ $u['has_pin']?'1':'0' }}" data-auto-delete="{{ $u['auto_delete'] }}" style="animation-delay:{{ $loop->index*30 }}ms">
            <div class="relative flex-shrink-0"><img src="{{ $u['avatar_url'] }}" class="w-11 h-11 rounded-2xl object-cover" alt=""><span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#0a0f1a] bg-slate-700 transition-colors" id="status-{{ $u['id'] }}"></span></div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between"><p class="text-white text-[13px] font-semibold truncate">{{ $u['name'] }}@if($u['has_pin']) 🔐@endif</p><span class="text-slate-600 text-[10px] ml-1.5 flex-shrink-0">{{ $u['last_msg_at'] ?? '' }}</span></div>
                <p class="text-slate-500 text-xs truncate mt-0.5" id="sidebar-msg-{{ $u['id'] }}">{{ $u['last_msg'] ?? 'Tap to chat' }}</p>
            </div>
            <span class="unread-badge hidden min-w-[18px] h-[18px] bg-brand-500 text-white text-[10px] font-bold rounded-full items-center justify-center px-1 shadow-lg shadow-brand-500/30 flex-shrink-0" id="unread-{{ $u['id'] }}" style="{{ ($u['unread']??0)>0?'display:flex':'' }}">{{ $u['unread']??0 }}</span>
        </button>
        @endforeach
    </div>
    <div class="px-4 py-2 border-t border-slate-800/30 flex items-center justify-center gap-1.5 text-slate-700 text-[10px] font-medium">🔒 Private & Encrypted</div>
</aside>
<!-- CHAT PANEL -->
<main class="flex-1 flex flex-col min-w-0 relative bg-[#030712]">
    <div id="chat-empty" class="flex-1 flex items-center justify-center flex-col text-center p-8">
        <div class="w-20 h-20 bg-gradient-to-br from-brand-500/10 to-indigo-500/10 rounded-3xl flex items-center justify-center border border-slate-800/50 mb-5"><svg class="w-10 h-10 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
        <h3 class="text-white text-lg font-bold mb-1">Select a conversation</h3>
        <p class="text-slate-600 text-sm">Choose a contact to start chatting securely</p>
    </div>
    <div id="chat-window" class="hidden flex-1 flex flex-col min-h-0">
        <div class="flex items-center gap-3 px-4 sm:px-5 h-[56px] border-b border-slate-800/40 glass flex-shrink-0 z-10">
            <button id="back-btn" class="sm:hidden w-8 h-8 rounded-xl bg-slate-800/50 flex items-center justify-center text-slate-400 hover:text-white transition flex-shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
            <div class="relative flex-shrink-0"><img id="chat-avatar" src="" class="w-9 h-9 rounded-2xl object-cover" alt=""><span id="chat-dot" class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-[#030712] bg-slate-600 transition-colors"></span></div>
            <div class="flex-1 min-w-0"><p id="chat-name" class="text-white text-sm font-bold truncate"></p><p id="chat-status" class="text-slate-500 text-[11px] font-medium"></p></div>
            <div class="flex items-center gap-1">
                <select id="ad-select" class="bg-slate-800/60 border border-slate-700/40 text-[10px] text-slate-400 rounded-lg px-1.5 py-1 focus:outline-none cursor-pointer" title="Auto-delete">
                    <option value="never">Never</option><option value="immediate">Instant</option><option value="5min">5 min</option><option value="seen">After Seen</option><option value="1day">1 day</option><option value="7day">7 days</option>
                </select>
                <button id="pin-toggle-btn" class="w-7 h-7 rounded-lg bg-slate-800/40 text-slate-600 hover:text-amber-400 flex items-center justify-center transition" title="Set PIN">🔐</button>
            </div>
        </div>
        <div id="chat-messages" class="flex-1 overflow-y-auto scrollbar-thin px-3 sm:px-5 py-4 space-y-1 chat-bg"></div>
        <div id="typing-bar" class="hidden px-4 py-1"><div class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-brand-500 rounded-full typB"></span><span class="w-1.5 h-1.5 bg-brand-500 rounded-full typB"></span><span class="w-1.5 h-1.5 bg-brand-500 rounded-full typB"></span><span class="text-slate-500 text-xs" id="typing-label"></span></div></div>
        <div id="reply-bar" class="hidden px-4 py-2 bg-slate-900/80 border-t border-slate-800/30"><div class="flex items-center gap-2"><div class="w-1 h-8 bg-indigo-500 rounded-full flex-shrink-0"></div><div class="flex-1 min-w-0"><p class="text-indigo-400 text-[10px] font-bold" id="reply-label">Replying</p><p class="text-slate-400 text-xs truncate" id="reply-text"></p></div><button id="cancel-reply" class="w-6 h-6 rounded-lg bg-slate-800 flex items-center justify-center text-slate-500 hover:text-white transition">✕</button></div></div>
        <div id="edit-bar" class="hidden px-4 py-2 bg-amber-900/20 border-t border-amber-500/20"><div class="flex items-center gap-2"><div class="w-1 h-8 bg-amber-500 rounded-full flex-shrink-0"></div><div class="flex-1 min-w-0"><p class="text-amber-400 text-[10px] font-bold">Editing message</p><p class="text-slate-400 text-xs truncate" id="edit-text"></p></div><button id="cancel-edit" class="w-6 h-6 rounded-lg bg-slate-800 flex items-center justify-center text-slate-500 hover:text-white transition">✕</button></div></div>
        <div class="px-3 sm:px-5 py-2.5 border-t border-slate-800/40 glass flex-shrink-0">
            <div class="flex items-end gap-2">
                <label class="w-9 h-9 rounded-xl bg-slate-800/50 hover:bg-slate-700 text-slate-500 hover:text-brand-400 flex items-center justify-center flex-shrink-0 mb-0.5 cursor-pointer transition"><input type="file" id="file-input" class="hidden" accept="image/*,video/*,audio/*,.pdf,.doc,.docx"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg></label>
                <div id="file-preview" class="hidden flex-shrink-0 relative"><div class="w-9 h-9 bg-slate-800 rounded-xl flex items-center justify-center overflow-hidden border border-slate-700/50"><img id="fp-img" src="" class="hidden w-full h-full object-cover" alt=""><span id="fp-name" class="text-[9px] text-white"></span></div><button id="clear-file" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white flex items-center justify-center text-[10px]">×</button></div>
                <textarea id="msg-input" rows="1" placeholder="Message…" class="flex-1 bg-slate-900/50 border border-slate-800/40 text-white text-sm rounded-2xl px-4 py-2.5 resize-none focus:outline-none focus:ring-1 focus:ring-brand-500/40 max-h-28 leading-snug placeholder-slate-600 transition" style="overflow-y:hidden"></textarea>
                <button id="send-btn" class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl flex items-center justify-center transition-all shadow-lg shadow-brand-500/20 hover:shadow-brand-500/40 active:scale-95 mb-0.5"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg></button>
            </div>
        </div>
    </div>
</main>
</div>
<!-- Context menu -->
<div id="ctx-menu" class="ctx-menu hidden">
    <div class="ctx-item" data-action="reply">↩️ Reply</div>
    <div class="ctx-item" data-action="react">😊 React</div>
    <div class="ctx-item" data-action="edit">✏️ Edit</div>
    <div class="ctx-item" data-action="copy">📋 Copy</div>
    <div class="ctx-item danger" data-action="delete">🗑️ Delete</div>
</div>
<!-- React picker -->
<div id="react-picker" class="hidden fixed z-[101] bg-[#1e293b] border border-slate-700/50 rounded-2xl p-2 flex gap-1 shadow-2xl"></div>
<!-- PIN modal -->
<div id="pin-modal" class="modal-bg"><div class="bg-[#0a0f1a] rounded-3xl w-full max-w-xs border border-slate-800/50 p-8 text-center"><div class="w-14 h-14 bg-amber-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-amber-500/20"><span class="text-2xl">🔐</span></div><h3 class="text-white font-bold mb-1" id="pin-title">Enter PIN</h3><p class="text-slate-500 text-sm mb-5" id="pin-subtitle">4-digit PIN required</p><div class="flex justify-center gap-3 mb-5" id="pin-dots"><div class="pin-dot"></div><div class="pin-dot"></div><div class="pin-dot"></div><div class="pin-dot"></div></div><input type="password" id="pin-input" maxlength="4" inputmode="numeric" pattern="[0-9]*" class="w-full bg-slate-900 border border-slate-700 text-center text-2xl tracking-[.5em] text-white rounded-2xl py-3 focus:outline-none focus:ring-2 focus:ring-brand-500/40" placeholder="• • • •"><p id="pin-error" class="text-red-400 text-xs mt-2 hidden">Wrong PIN</p><div class="flex gap-2 mt-5"><button id="pin-remove" class="hidden flex-1 bg-red-500/10 hover:bg-red-500/20 text-red-500 font-bold rounded-xl py-2.5 text-sm transition">Disable</button><button id="pin-cancel" class="flex-1 bg-slate-800 text-slate-400 font-bold rounded-xl py-2.5 text-sm hover:bg-slate-700 transition">Cancel</button><button id="pin-submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl py-2.5 text-sm transition">Confirm</button></div></div></div>
<!-- Avatar modal -->
<div id="avatar-modal" class="modal-bg"><div class="bg-[#0a0f1a] rounded-3xl w-full max-w-xs border border-slate-800/50 p-8 text-center"><img id="avatar-preview" src="{{ $currentUser->avatar_url }}" class="w-24 h-24 rounded-3xl object-cover mx-auto mb-4 ring-4 ring-brand-500/20" alt=""><h3 class="text-white font-bold mb-4">Change Profile Photo</h3><label class="block w-full bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl py-2.5 text-sm cursor-pointer transition mb-3">📷 Choose Photo<input type="file" id="avatar-file" class="hidden" accept="image/*"></label><div class="flex gap-2"><button id="avatar-cancel" class="flex-1 bg-slate-800 text-slate-400 font-bold rounded-xl py-2.5 text-sm hover:bg-slate-700 transition">Cancel</button><button id="avatar-save" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl py-2.5 text-sm transition">Save</button></div></div></div>
<!-- Session modal -->
<div id="sess-modal" class="modal-bg"><div class="bg-[#0a0f1a] rounded-3xl w-full max-w-xs border border-slate-800/50 p-8 text-center"><h3 class="text-white font-bold mb-1">Session Expired</h3><p class="text-slate-500 text-sm mb-5">Please log in again.</p><button onclick="location.href=APP.routes.news" class="w-full bg-brand-500 text-white font-bold rounded-2xl py-2.5 text-sm">Go to NewsHub</button></div></div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
const EMOJIS=['❤️','😂','👍','👎','😮','😢','🔥','🎉','💯','🙏'];
let activeUserId=null,activeUserName='',pendingFile=null,lastDate='',pollTimer=null,lastKnownMsgId=0;
let oldestMsgId=0,loadingOlder=false;
let replyToMsg=null,editMsgId=null,typingTimer=null,isTyping=false;
let ctxMsgId=null,ctxMsgMine=false,ctxMsgContent='';
let isMuted=localStorage.getItem('chat_muted')==='1';

// NOTIFICATION SOUND (Web Audio API — pleasant two-tone beep)
let audioCtx=null;
function playNotifSound(){
    if(isMuted)return;
    try{
        if(!audioCtx)audioCtx=new(window.AudioContext||window.webkitAudioContext)();
        if(audioCtx.state==='suspended')audioCtx.resume();
        const t=audioCtx.currentTime;
        // First tone
        const o1=audioCtx.createOscillator();const g1=audioCtx.createGain();
        o1.type='sine';o1.frequency.value=880;g1.gain.setValueAtTime(0.15,t);g1.gain.exponentialRampToValueAtTime(0.01,t+0.15);
        o1.connect(g1);g1.connect(audioCtx.destination);o1.start(t);o1.stop(t+0.15);
        // Second tone (higher)
        const o2=audioCtx.createOscillator();const g2=audioCtx.createGain();
        o2.type='sine';o2.frequency.value=1320;g2.gain.setValueAtTime(0.12,t+0.12);g2.gain.exponentialRampToValueAtTime(0.01,t+0.3);
        o2.connect(g2);g2.connect(audioCtx.destination);o2.start(t+0.12);o2.stop(t+0.3);
    }catch(e){}
}
const grantNotifs = () => {
    if(audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
    if ('Notification' in window && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
        Notification.requestPermission();
    }
};
document.body.addEventListener('click', grantNotifs, {once: true});
document.body.addEventListener('touchstart', grantNotifs, {once: true});

// MUTE TOGGLE
function updateMuteUI(){document.getElementById('mute-btn').textContent=isMuted?'🔕':'🔔';document.getElementById('mute-btn').title=isMuted?'Unmute notifications':'Mute notifications';}
updateMuteUI();
document.getElementById('mute-btn').addEventListener('click',()=>{isMuted=!isMuted;localStorage.setItem('chat_muted',isMuted?'1':'0');updateMuteUI();});
let pinCallback=null,curAutoDelete='never';

// NAV
let navLevel=0;history.replaceState({level:0},'');
function pushNav(l){navLevel=l;history.pushState({level:l},'');}
window.addEventListener('popstate',e=>{if((e.state?.level??0)===0&&navLevel===1){navLevel=0;closeChatToSidebar();}else doLogoutAndExit();});

// PRIVACY : Top Class Blank Screen for Screen Recording / Blur
let _ht=null;
document.addEventListener('visibilitychange',()=>{
    if(document.visibilityState==='hidden'){
        document.body.classList.add('privacy-active');
        _ht=setTimeout(()=>{
            const f=new FormData();f.append('_token',APP.csrfToken);
            navigator.sendBeacon(APP.routes.logout,f);
            window.location.replace(APP.routes.news);
        },10000); // 10s auto logout
    } else {
        document.body.classList.remove('privacy-active');
        clearTimeout(_ht);
        if(activeUserId)refreshChat();
    }
});
window.addEventListener('blur', ()=>document.body.classList.add('privacy-active'));
window.addEventListener('focus', ()=>document.body.classList.remove('privacy-active'));
document.getElementById('privacy-overlay').addEventListener('click', ()=>document.body.classList.remove('privacy-active'));

document.addEventListener('keydown',e=>{if(e.key==='Escape'){hideCtx();hideRP();if(!document.getElementById('pin-modal').classList.contains('show'))doLogoutAndExit();}if((e.ctrlKey||e.metaKey)&&['p','s'].includes(e.key.toLowerCase()))e.preventDefault();});
async function doLogoutAndExit(){try{await fetch(APP.routes.logout,{method:'POST',headers:{'X-CSRF-TOKEN':APP.csrfToken}});}catch(e){}window.location.replace(APP.routes.news);}
document.getElementById('exit-btn').addEventListener('click',doLogoutAndExit);

// PUSHER
let pusherOk=false;
try{const P=new Pusher('{{ config("broadcasting.connections.pusher.key") }}',{cluster:'{{ config("broadcasting.connections.pusher.options.cluster") }}',authEndpoint:'/broadcasting/auth',auth:{headers:{'X-CSRF-TOKEN':APP.csrfToken}}});P.subscribe('private-chat.'+APP.currentUser.id).bind('new.message',onMsg);P.connection.bind('connected',()=>{pusherOk=true;});}catch(e){}
startPoll();

function onMsg(d){const box=document.getElementById('chat-messages');if((d.sender_id===activeUserId||d.receiver_id===activeUserId)&&box&&!box.querySelector(`[data-mid="${d.id}"]`))appendMsg(d,d.sender_id===APP.currentUser.id);const uid=d.sender_id===APP.currentUser.id?d.receiver_id:d.sender_id;sidebarUpdate(uid,d.content);if(d.sender_id!==APP.currentUser.id&&d.sender_id!==activeUserId){const b=document.getElementById('unread-'+d.sender_id);if(b){b.textContent=parseInt(b.textContent||0)+1;b.style.display='flex';}}if(d.sender_id!==APP.currentUser.id)playNotifSound();}

// POLLING 2s
function startPoll(){if(pollTimer)return;pollTimer=setInterval(async()=>{if(!activeUserId)return;try{const u=APP.routes.messages+activeUserId+(lastKnownMsgId?'?after='+lastKnownMsgId:'');const r=await apiFetch(u);const d=await r.json();const box=document.getElementById('chat-messages');let gotNew=false;d.messages.forEach(m=>{if(!box.querySelector(`[data-mid="${m.id}"]`)){appendMsg(m,m.is_mine);if(!m.is_mine)gotNew=true;}if(m.id>lastKnownMsgId)lastKnownMsgId=m.id;});if(gotNew)playNotifSound();if(d.other_user)updateStatus(d.other_user);
// Flip ticks: if all sent msgs are read, turn gray ✓✓ to blue ✓✓
if(d.unread_by_other===0){box.querySelectorAll('[data-mine="1"]').forEach(el=>{el.querySelectorAll('span').forEach(s=>{if(s.textContent.trim()==='✓✓'&&!s.classList.contains('text-blue-400')){s.className='text-blue-400 text-[10px] font-bold ml-1';}});});}
}catch(e){}},2000);}

function updateStatus(u){
    const st=document.getElementById('chat-status'),dot=document.getElementById('chat-dot'),tb=document.getElementById('typing-bar'),tl=document.getElementById('typing-label'),inp=document.getElementById('msg-input'),sb=document.getElementById('sidebar-msg-'+activeUserId),sbd=document.getElementById('status-'+activeUserId);
    if(u.is_typing){st.textContent='typing…';st.className='text-emerald-400 text-[11px] font-medium';tb.classList.remove('hidden');tl.textContent=activeUserName+' is typing…';inp.placeholder=activeUserName+' is typing…';if(sb){sb.textContent='typing…';sb.className='text-emerald-400 text-xs truncate mt-0.5';}}
    else{st.textContent=u.is_online?'🟢 Online':(u.last_seen?'Last seen '+u.last_seen:'Offline');st.className=(u.is_online?'text-emerald-400':'text-slate-500')+' text-[11px] font-medium';tb.classList.add('hidden');inp.placeholder='Message…';if(sb&&sb.textContent==='typing…'){sb.textContent='Tap to chat';sb.className='text-slate-500 text-xs truncate mt-0.5';}}
    dot.className='absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-[#030712] transition-colors '+(u.is_online?'bg-emerald-500 onP':'bg-slate-600');
    if(sbd)sbd.className='absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-[#0a0f1a] transition-colors '+(u.is_online?'bg-emerald-500 onP':'bg-slate-700');
}

async function refreshChat(){if(!activeUserId)return;try{const u=APP.routes.messages+activeUserId+(lastKnownMsgId?'?after='+lastKnownMsgId:'');const r=await apiFetch(u);const d=await r.json();const box=document.getElementById('chat-messages');d.messages.forEach(m=>{if(!box.querySelector(`[data-mid="${m.id}"]`))appendMsg(m,m.is_mine);if(m.id>lastKnownMsgId)lastKnownMsgId=m.id;});}catch(e){}}

// OPEN CHAT
document.querySelectorAll('.user-item').forEach(b=>b.addEventListener('click',()=>{
    const uid=b.dataset.userId,name=b.dataset.name,avatar=b.dataset.avatar,hasPin=b.dataset.hasPin==='1',ad=b.dataset.autoDelete||'never';
    if(hasPin){showPinModal('Enter PIN for '+name,'Verify',pin=>{verifyPin(uid,pin,()=>doOpenChat(uid,name,avatar,ad));});}
    else doOpenChat(uid,name,avatar,ad);
}));

async function doOpenChat(uid,name,avatar,ad){
    activeUserId=parseInt(uid);activeUserName=name;lastDate='';replyToMsg=null;editMsgId=null;curAutoDelete=ad;
    document.getElementById('reply-bar').classList.add('hidden');document.getElementById('edit-bar').classList.add('hidden');document.getElementById('typing-bar').classList.add('hidden');
    document.querySelectorAll('.user-item').forEach(b=>b.classList.remove('active'));document.querySelector(`[data-user-id="${uid}"]`)?.classList.add('active');
    document.getElementById('chat-empty').classList.add('hidden');const cw=document.getElementById('chat-window');cw.classList.remove('hidden');cw.classList.add('flex');
    if(window.innerWidth<640)document.getElementById('sidebar').style.transform='translateX(-100%)';pushNav(1);
    document.getElementById('ad-select').value=ad;
    const badge=document.getElementById('unread-'+uid);if(badge){badge.style.display='none';badge.textContent='0';}
    try{const r=await apiFetch(APP.routes.messages+uid);const d=await r.json();
        document.getElementById('chat-avatar').src=d.other_user.avatar_url||avatar||'';document.getElementById('chat-name').textContent=d.other_user.name||name;
        updateStatus(d.other_user);if(d.settings)curAutoDelete=d.settings.auto_delete||'never';document.getElementById('ad-select').value=curAutoDelete;
        const box=document.getElementById('chat-messages');box.innerHTML='';lastDate='';lastKnownMsgId=0;oldestMsgId=0;
        d.messages.forEach(m=>{appendMsg(m,m.is_mine);if(m.id>lastKnownMsgId)lastKnownMsgId=m.id;if(oldestMsgId===0||m.id<oldestMsgId)oldestMsgId=m.id;});scrollEnd();document.getElementById('msg-input').focus();
    }catch(e){chkSess();}
}

// SCROLL-UP LAZY LOADING (load older messages)
document.getElementById('chat-messages').addEventListener('scroll',async function(){
    if(this.scrollTop<60&&!loadingOlder&&activeUserId&&oldestMsgId>0){
        loadingOlder=true;
        const spinner=document.createElement('div');spinner.id='load-older';spinner.className='text-center py-3';spinner.innerHTML='<svg class="w-5 h-5 animate-spin text-brand-500 mx-auto" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" class="opacity-75"></path></svg>';
        this.prepend(spinner);const prevH=this.scrollHeight;
        try{
            const r=await apiFetch(APP.routes.messages+activeUserId+'?before='+oldestMsgId+'&per_page=30');
            const d=await r.json();
            spinner.remove();
            if(d.messages.length===0){loadingOlder=true;return;}
            const frag=document.createDocumentFragment();let tmpDate='';
            d.messages.forEach(m=>{
                const el=createMsgEl(m,m.is_mine);
                frag.appendChild(el);
                if(m.id<oldestMsgId||oldestMsgId===0)oldestMsgId=m.id;
            });
            this.prepend(frag);
            this.scrollTop=this.scrollHeight-prevH;
        }catch(e){spinner.remove();}
        loadingOlder=false;
    }
});

function closeChatToSidebar(){if(window.innerWidth<640)document.getElementById('sidebar').style.transform='';document.getElementById('chat-window').classList.add('hidden');document.getElementById('chat-window').classList.remove('flex');document.getElementById('chat-empty').classList.remove('hidden');activeUserId=null;lastKnownMsgId=0;}
document.getElementById('back-btn').addEventListener('click',()=>{navLevel=0;closeChatToSidebar();history.replaceState({level:0},'');});

// AUTO-DELETE
document.getElementById('ad-select').addEventListener('change',async function(){curAutoDelete=this.value;if(!activeUserId)return;try{await apiFetch(APP.routes.autoDelete+activeUserId,'POST',{auto_delete:curAutoDelete});}catch(e){}const b=document.querySelector(`[data-user-id="${activeUserId}"]`);if(b)b.dataset.autoDelete=curAutoDelete;});

// PIN
document.getElementById('pin-toggle-btn').addEventListener('click',()=>{if(!activeUserId)return;
    const b=document.querySelector(`[data-user-id="${activeUserId}"]`);const hasPin=b?.dataset.hasPin==='1';
    if(hasPin){
        document.getElementById('pin-remove').classList.remove('hidden');
        showPinModal('Change or Remove PIN','Update',async pin=>{
            try{await apiFetch(APP.routes.setPin+activeUserId,'POST',{pin});closePinModal();}catch(e){}
        });
    } else {
        document.getElementById('pin-remove').classList.add('hidden');
        showPinModal('Set 4-digit PIN','Enable PIN',async pin=>{
            try{await apiFetch(APP.routes.setPin+activeUserId,'POST',{pin});if(b)b.dataset.hasPin='1';closePinModal();}catch(e){}
        });
    }
});

document.getElementById('pin-remove').addEventListener('click', async () => {
    if(!activeUserId) return;
    const b=document.querySelector(`[data-user-id="${activeUserId}"]`);
    if(confirm('Are you sure you want to disable PIN protection for this chat?')) {
        try { await apiFetch(APP.routes.removePin+activeUserId,'DELETE'); if(b)b.dataset.hasPin='0'; closePinModal(); } catch(e){}
    }
});

function showPinModal(title,btnText,cb){pinCallback=cb;document.getElementById('pin-title').textContent=title;document.getElementById('pin-submit').textContent=btnText;document.getElementById('pin-input').value='';document.getElementById('pin-error').classList.add('hidden');updatePinDots(0);document.getElementById('pin-modal').classList.add('show');document.getElementById('pin-input').focus();}
function closePinModal(){document.getElementById('pin-modal').classList.remove('show');pinCallback=null;}
document.getElementById('pin-cancel').addEventListener('click',closePinModal);
document.getElementById('pin-input').addEventListener('input',function(){const l=this.value.length;updatePinDots(l);if(l===4)document.getElementById('pin-submit').click();});
document.getElementById('pin-submit').addEventListener('click',()=>{const pin=document.getElementById('pin-input').value;if(pin.length!==4)return;if(pinCallback)pinCallback(pin);});
function updatePinDots(n){document.querySelectorAll('#pin-dots .pin-dot').forEach((d,i)=>{d.classList.toggle('filled',i<n);});}
async function verifyPin(uid,pin,onSuccess){try{const r=await apiFetch(APP.routes.verifyPin+uid+'/verify','POST',{pin});const d=await r.json();if(d.success){closePinModal();onSuccess();}else{document.getElementById('pin-error').classList.remove('hidden');document.getElementById('pin-input').value='';updatePinDots(0);}}catch(e){document.getElementById('pin-error').classList.remove('hidden');document.getElementById('pin-input').value='';updatePinDots(0);}}

// AVATAR
document.getElementById('avatar-area').addEventListener('click',()=>{document.getElementById('avatar-modal').classList.add('show');});
document.getElementById('avatar-cancel').addEventListener('click',()=>{document.getElementById('avatar-modal').classList.remove('show');});
document.getElementById('avatar-file').addEventListener('change',function(){if(this.files[0]){document.getElementById('avatar-preview').src=URL.createObjectURL(this.files[0]);}});
document.getElementById('avatar-save').addEventListener('click',async()=>{
    const btn = document.getElementById('avatar-save');
    const f=document.getElementById('avatar-file').files[0];
    if(!f) return;
    btn.disabled = true;
    btn.textContent = 'Saving...';
    const fd=new FormData();
    fd.append('avatar',f);
    try{
        const r=await fetch(APP.routes.avatar,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':APP.csrfToken,
                'Accept':'application/json'
            },
            body:fd
        });
        const d=await r.json();
        if(d.success){
            document.getElementById('my-avatar').src=d.avatar_url;
            document.getElementById('avatar-modal').classList.remove('show');
        } else {
            alert(d.message || d.error || 'Upload failed');
        }
    }catch(e){
        alert('Network or Server Error: ' + e.message);
    }
    btn.disabled = false;
    btn.textContent = 'Save';
});

// APPEND MSG
function appendMsg(msg,mine){
    const box=document.getElementById('chat-messages'),ds=msg.date||new Date().toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
    if(ds!==lastDate){lastDate=ds;const s=document.createElement('div');s.className='flex justify-center my-3';s.innerHTML=`<span class="bg-slate-900/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full border border-slate-800/40">${ds}</span>`;box.appendChild(s);}
    const el=document.createElement('div');el.className=`flex ${mine?'justify-end':'justify-start'} mb-1.5 msg-anim`;el.dataset.mid=msg.id;el.dataset.mine=mine?'1':'0';el.dataset.content=msg.content||'';
    let rH='';if(msg.reply_to)rH=`<div class="reply-preview cursor-pointer" onclick="scrollToMsg(${msg.reply_to.id})"><p class="text-[10px] font-bold ${msg.reply_to.is_mine?'text-brand-400':'text-indigo-400'}">${msg.reply_to.is_mine?'You':activeUserName}</p><p class="text-slate-400 text-[11px] truncate">${esc(msg.reply_to.content)}</p></div>`;
    let body='';if(msg.type==='image'&&msg.file_path)body=`<img src="${msg.file_path}" class="rounded-xl max-w-full max-h-52 object-contain cursor-pointer" onclick="window.open('${msg.file_path}','_blank')">`;else if(msg.type==='file'&&msg.file_path)body=`<a href="${msg.file_path}" target="_blank" class="text-indigo-300 text-sm underline">📎 ${esc(msg.content)}</a>`;else body=`<p class="text-[13px] leading-relaxed whitespace-pre-wrap break-words msg-text">${esc(msg.content)}</p>`;
    let rxH='';const rx=msg.reactions||{};const rk=Object.keys(rx);if(rk.length>0){const c={};rk.forEach(k=>{const e=rx[k];c[e]=(c[e]||0)+1;});let s='';Object.entries(c).forEach(([em,n])=>{s+=`<span class="text-xs bg-slate-800/80 border border-slate-700/30 px-1.5 py-0.5 rounded-full cursor-pointer hover:bg-slate-700" onclick="doReact(${msg.id},'${em}')">${em}${n>1?' '+n:''}</span>`;});rxH=`<div class="flex items-center gap-1 mt-1 flex-wrap ${mine?'justify-end':''}">${s}</div>`;}
    let tH='';if(mine)tH=msg.read_at?'<span class="text-blue-400 text-[10px] font-bold ml-1">✓✓</span>':'<span class="text-white/30 text-[10px] font-bold ml-1">✓✓</span>';
    const bc=mine?'bg-gradient-to-br from-brand-500 to-rose-600 text-white rounded-2xl rounded-br-sm shadow-md shadow-brand-500/15':'bg-slate-800/80 text-slate-100 rounded-2xl rounded-bl-sm border border-slate-700/40';
    el.innerHTML=`<div class="msg-bubble relative"><div class="${bc} px-3.5 py-2 cursor-pointer" onclick="showCtx(event,${msg.id},${mine},'${esc(msg.content).replace(/'/g,"\\'").replace(/\n/g,' ')}')">${rH}${body}<div class="flex items-center gap-1 mt-0.5 ${mine?'justify-end':''}">\<span class="${mine?'text-white/50':'text-slate-500'} text-[10px]">${msg.created_at||''}</span>${tH}</div></div>${rxH}</div>`;
    box.appendChild(el);scrollEnd();
}
function scrollToMsg(id){const el=document.querySelector(`[data-mid="${id}"]`);if(el){el.scrollIntoView({behavior:'smooth',block:'center'});el.style.background='rgba(99,102,241,.15)';el.style.borderRadius='12px';setTimeout(()=>{el.style.background='';},2000);}}

// CONTEXT MENU
function showCtx(e,id,mine,content){e.preventDefault();e.stopPropagation();ctxMsgId=id;ctxMsgMine=mine;ctxMsgContent=content;const m=document.getElementById('ctx-menu');m.style.left=Math.min(e.clientX,innerWidth-180)+'px';m.style.top=Math.min(e.clientY,innerHeight-220)+'px';m.classList.remove('hidden');m.querySelector('[data-action="edit"]').style.display=mine?'':'none';}
function hideCtx(){document.getElementById('ctx-menu').classList.add('hidden');}
function hideRP(){document.getElementById('react-picker').classList.add('hidden');}
document.addEventListener('click',e=>{if(!e.target.closest('#ctx-menu')&&!e.target.closest('.msg-bubble'))hideCtx();if(!e.target.closest('#react-picker'))hideRP();});
document.querySelectorAll('.ctx-item').forEach(i=>i.addEventListener('click',()=>{const a=i.dataset.action;hideCtx();
    if(a==='reply')setReply(ctxMsgId,ctxMsgContent,ctxMsgMine);
    else if(a==='react')showRP();
    else if(a==='edit')startEdit(ctxMsgId,ctxMsgContent);
    else if(a==='copy'){navigator.clipboard.writeText(ctxMsgContent).catch(()=>{});}
    else if(a==='delete')delMsg(ctxMsgId);
}));

// REACT
function showRP(){const p=document.getElementById('react-picker');p.innerHTML=EMOJIS.map(e=>`<span class="text-xl p-1.5 rounded-lg cursor-pointer hover:bg-white/10 hover:scale-125 transition-all" onclick="doReact(${ctxMsgId},'${e}')">${e}</span>`).join('');const el=document.querySelector(`[data-mid="${ctxMsgId}"]`);if(el){const r=el.getBoundingClientRect();p.style.left=Math.min(r.left,innerWidth-340)+'px';p.style.top=Math.max(r.top-50,10)+'px';}p.classList.remove('hidden');}
async function doReact(id,em){hideRP();try{await apiFetch(APP.routes.react+id,'POST',{emoji:em});}catch(e){}}

// REPLY
function setReply(id,c,mine){replyToMsg={id,content:c.slice(0,80)};editMsgId=null;document.getElementById('edit-bar').classList.add('hidden');document.getElementById('reply-bar').classList.remove('hidden');document.getElementById('reply-label').textContent=mine?'Replying to yourself':'Replying to '+activeUserName;document.getElementById('reply-text').textContent=c.slice(0,80);document.getElementById('msg-input').focus();}
document.getElementById('cancel-reply').addEventListener('click',()=>{replyToMsg=null;document.getElementById('reply-bar').classList.add('hidden');});

// EDIT
function startEdit(id,c){editMsgId=id;replyToMsg=null;document.getElementById('reply-bar').classList.add('hidden');document.getElementById('edit-bar').classList.remove('hidden');document.getElementById('edit-text').textContent=c.slice(0,80);const inp=document.getElementById('msg-input');inp.value=c;inp.focus();inp.style.height='auto';inp.style.height=Math.min(inp.scrollHeight,112)+'px';}
document.getElementById('cancel-edit').addEventListener('click',()=>{editMsgId=null;document.getElementById('edit-bar').classList.add('hidden');document.getElementById('msg-input').value='';});

// TYPING
document.getElementById('msg-input').addEventListener('input',function(){this.style.height='auto';this.style.height=Math.min(this.scrollHeight,112)+'px';if(!isTyping&&activeUserId&&!editMsgId){isTyping=true;apiFetch(APP.routes.typing+activeUserId,'POST');}clearTimeout(typingTimer);typingTimer=setTimeout(()=>{isTyping=false;},3000);});

// SEND / EDIT SUBMIT
document.getElementById('send-btn').addEventListener('click',doSend);
document.getElementById('msg-input').addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();doSend();}});
async function doSend(){
    if(!activeUserId)return;const inp=document.getElementById('msg-input');const txt=inp.value.trim();
    if(editMsgId){if(!txt)return;try{await apiFetch(APP.routes.editMsg+editMsgId,'PUT',{content:txt});const el=document.querySelector(`[data-mid="${editMsgId}"] .msg-text`);if(el)el.textContent=txt;el?.closest('[data-mid]')&&(el.closest('[data-mid]').dataset.content=txt);}catch(e){}editMsgId=null;document.getElementById('edit-bar').classList.add('hidden');inp.value='';inp.style.height='auto';inp.focus();return;}
    if(!txt&&!pendingFile)return;const fd=new FormData();fd.append('receiver_id',activeUserId);if(txt)fd.append('content',txt);if(pendingFile)fd.append('file',pendingFile);if(replyToMsg)fd.append('reply_to_id',replyToMsg.id);
    const tid='tmp_'+Date.now(),now=new Date(),ts=now.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',hour12:true});
    appendMsg({id:tid,content:txt||(pendingFile?pendingFile.name:''),type:pendingFile?(pendingFile.type.startsWith('image/')?'image':'file'):'text',file_path:null,created_at:ts,read_at:null,date:now.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}),reactions:{},reply_to:replyToMsg?{id:replyToMsg.id,content:replyToMsg.content,is_mine:true}:null},true);
    sidebarUpdate(activeUserId,txt||'File');inp.value='';inp.style.height='auto';clearFile();inp.focus();replyToMsg=null;document.getElementById('reply-bar').classList.add('hidden');
    try{const r=await fetch(APP.routes.send,{method:'POST',headers:{'X-CSRF-TOKEN':APP.csrfToken},body:fd});const d=await r.json();if(d.success){const te=document.querySelector(`[data-mid="${tid}"]`);if(te)te.dataset.mid=d.message.id;if(d.message.id>lastKnownMsgId)lastKnownMsgId=d.message.id;
    // Auto-delete timer
    if(curAutoDelete==='immediate')setTimeout(()=>delMsg(d.message.id),1000);else if(curAutoDelete==='5min')setTimeout(()=>delMsg(d.message.id),300000);else if(curAutoDelete==='1day')setTimeout(()=>delMsg(d.message.id),86400000);}}catch(e){document.querySelector(`[data-mid="${tid}"]`)?.remove();chkSess();}
}

// DELETE
async function delMsg(id){const el=document.querySelector(`[data-mid="${id}"]`);if(el){el.style.transition='all .25s';el.style.opacity='0';el.style.transform='scale(.8)';setTimeout(()=>el.remove(),250);}try{await apiFetch(APP.routes.delMsg+id,'DELETE');}catch(e){}}

// FILE
document.getElementById('file-input').addEventListener('change',function(){pendingFile=this.files[0];if(!pendingFile)return;document.getElementById('file-preview').classList.remove('hidden');if(pendingFile.type.startsWith('image/')){document.getElementById('fp-img').src=URL.createObjectURL(pendingFile);document.getElementById('fp-img').classList.remove('hidden');document.getElementById('fp-name').classList.add('hidden');}else{document.getElementById('fp-name').textContent=pendingFile.name.slice(0,8)+'…';document.getElementById('fp-img').classList.add('hidden');document.getElementById('fp-name').classList.remove('hidden');}});
document.getElementById('clear-file').addEventListener('click',clearFile);
function clearFile(){pendingFile=null;document.getElementById('file-input').value='';document.getElementById('file-preview').classList.add('hidden');}

// SEARCH
document.getElementById('user-search').addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('.user-item').forEach(el=>{el.style.display=el.dataset.name.toLowerCase().includes(q)?'':'none';});});

// SESSION
async function chkSess(){try{const r=await fetch(APP.routes.sessChk);const d=await r.json();if(!d.authenticated){document.getElementById('sess-modal').classList.add('show');}}catch(e){}}

// PUSH
async function setupPush(){if(!('PushManager' in window))return;try{const reg=await navigator.serviceWorker.ready;const sub=await reg.pushManager.getSubscription()||await reg.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:b64arr(APP.vapidKey)});await apiFetch(APP.routes.pushSub,'POST',sub.toJSON());}catch(e){}}
if('serviceWorker' in navigator)navigator.serviceWorker.register('/sw.js').then(setupPush).catch(()=>{});

// HELPERS
function scrollEnd(){const b=document.getElementById('chat-messages');requestAnimationFrame(()=>{b.scrollTop=b.scrollHeight;});}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function apiFetch(u,m='GET',b=null){const o={method:m,headers:{'X-CSRF-TOKEN':APP.csrfToken,'X-Requested-With':'XMLHttpRequest'}};if(b){o.headers['Content-Type']='application/json';o.body=JSON.stringify(b);}return fetch(u,o);}
function sidebarUpdate(uid,c){const el=document.getElementById('sidebar-msg-'+uid);if(el){el.textContent=c;el.className='text-slate-500 text-xs truncate mt-0.5';}}
function b64arr(s){if(!s)return new Uint8Array(0);const p='='.repeat((4-s.length%4)%4);const b=atob((s+p).replace(/-/g,'+').replace(/_/g,'/'));return Uint8Array.from([...b].map(c=>c.charCodeAt(0)));}
</script>
</body>
</html>
