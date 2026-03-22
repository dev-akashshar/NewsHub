<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tab-btn.active    { background: rgb(99 102 241 / .15); color: #818cf8; border-color: rgb(99 102 241 / .4); }
        .modal-overlay     { background: rgba(0,0,0,.7); backdrop-filter: blur(4px); }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

<script>
window.APP = {
    csrfToken: document.querySelector('meta[name="csrf-token"]').content,
    currentUserId: {{ session('hidden_user_id') ?? 'null' }},
    routes: {
        logout:         '{{ route("hidden.logout") }}',
        sessionCheck:   '{{ route("hidden.session-check") }}',
        chat:           '{{ route("chat.index") }}',
        news:           '{{ route("news.index") }}',
        users:          '/admin/users',
        settings:       '{{ route("admin.settings.update") }}',
        messages:       '{{ route("admin.messages") }}',
        deleteMessage:  '/admin/messages/',
    },
};
window.__hiddenModeActive = true;
</script>

<!-- ── TOP NAV ─────────────────────────────────────────────── -->
<nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30 h-14 flex items-center px-4 sm:px-6 gap-4">
    <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
        </div>
        <span class="font-bold text-white">Admin Panel</span>
    </div>
    <div class="flex-1"></div>
    <a href="{{ route('chat.index') }}" class="text-slate-400 hover:text-white text-sm transition">💬 Chat</a>
    <button id="logout-btn" class="text-slate-400 hover:text-red-400 text-sm transition">Logout</button>
</nav>

<!-- ── LAYOUT ──────────────────────────────────────────────── -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label'=>'Total Users',    'value'=>$stats['total_users'],    'color'=>'indigo', 'icon'=>'👤'],
            ['label'=>'Admins',         'value'=>$stats['total_admins'],   'color'=>'purple', 'icon'=>'👑'],
            ['label'=>'Messages',       'value'=>$stats['total_messages'], 'color'=>'cyan',   'icon'=>'💬'],
            ['label'=>'Online Now',     'value'=>$stats['active_users'],   'color'=>'green',  'icon'=>'🟢'],
        ] as $s)
        <div class="bg-slate-900 rounded-xl border border-slate-800 p-4">
            <div class="text-2xl mb-1">{{ $s['icon'] }}</div>
            <p class="text-2xl font-bold text-white">{{ $s['value'] }}</p>
            <p class="text-slate-500 text-xs mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-5 overflow-x-auto">
        @foreach(['users'=>'👥 Users', 'messages'=>'💬 Messages', 'settings'=>'⚙️ Settings'] as $tab => $label)
        <button class="tab-btn flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium text-slate-400 border border-transparent transition
                       {{ $tab === 'users' ? 'active' : '' }}"
                data-tab="{{ $tab }}">{{ $label }}</button>
        @endforeach
    </div>

    <!-- ── USERS TAB ─────────────────────────────────────────── -->
    <div id="tab-users" class="tab-content">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-white font-semibold">User Management</h2>
            <button id="add-user-btn"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add User
            </button>
        </div>

        <!-- Search -->
        <input id="user-search" type="text" placeholder="Search users…"
               class="w-full sm:w-72 bg-slate-800 border border-slate-700 text-sm text-white placeholder-slate-500 rounded-xl px-4 py-2.5 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">

        <!-- Table -->
        <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Username</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Last Seen</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody" class="divide-y divide-slate-800">
                        @foreach($users as $u)
                        <tr class="user-row hover:bg-slate-800/30 transition" data-user-id="{{ $u->id }}"
                            data-name="{{ strtolower($u->name) }}" data-username="{{ strtolower($u->username) }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                                    <span class="text-white font-medium">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-400 font-mono">{{ $u->username }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $u->role === 'admin' ? 'bg-purple-900/50 text-purple-300 border border-purple-700/50' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                    {{ $u->role === 'admin' ? '👑 Admin' : '👤 User' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button class="toggle-status flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border transition
                                    {{ $u->is_active ? 'bg-green-900/30 text-green-400 border-green-800' : 'bg-red-900/30 text-red-400 border-red-800' }}"
                                        data-user-id="{{ $u->id }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $u->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                    {{ $u->is_active ? 'Active' : 'Blocked' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">
                                {{ $u->last_seen?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <!-- Reset Password -->
                                    <button class="reset-pw-btn text-slate-500 hover:text-amber-400 transition"
                                            title="Reset Password" data-user-id="{{ $u->id }}" data-name="{{ $u->name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </button>
                                    <!-- Edit -->
                                    <button class="edit-user-btn text-slate-500 hover:text-indigo-400 transition"
                                            title="Edit"
                                            data-user="{{ json_encode(['id'=>$u->id, 'name'=>$u->name, 'username'=>$u->username, 'role'=>$u->role]) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <!-- Delete -->
                                    @if($u->id !== session('hidden_user_id'))
                                    <button class="delete-user-btn text-slate-500 hover:text-red-400 transition"
                                            title="Delete" data-user-id="{{ $u->id }}" data-name="{{ $u->name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── MESSAGES TAB ──────────────────────────────────────── -->
    <div id="tab-messages" class="tab-content hidden">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-white font-semibold">Message Monitor</h2>
            <select id="msg-user-filter" class="bg-slate-800 border border-slate-700 text-sm text-white rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Users</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="messages-list" class="space-y-2">
            @foreach($recentMessages as $msg)
            <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 flex items-start gap-3 hover:border-slate-700 transition msg-row" data-msg-id="{{ $msg->id }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-indigo-400 text-xs font-semibold">{{ $msg->sender->name }}</span>
                        <svg class="w-3 h-3 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <span class="text-slate-400 text-xs">{{ $msg->receiver->name }}</span>
                        <span class="ml-auto text-slate-600 text-xs flex-shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-300 text-sm truncate">{{ $msg->content }}</p>
                </div>
                <button class="delete-msg-btn flex-shrink-0 text-slate-600 hover:text-red-400 transition" data-msg-id="{{ $msg->id }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ── SETTINGS TAB ──────────────────────────────────────── -->
    <div id="tab-settings" class="tab-content hidden">
        <h2 class="text-white font-semibold mb-5">System Settings</h2>
        <div class="max-w-xl space-y-5">

            <!-- App Name -->
            <div class="bg-slate-900 rounded-xl border border-slate-800 p-5">
                <label class="block text-sm font-medium text-slate-300 mb-2">App Name</label>
                <input id="set-app-name" type="text" value="{{ $settings['app_name'] }}"
                       class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-slate-500 text-xs mt-1.5">Shown in navbar and page titles.</p>
            </div>

            <!-- Logo Click Count -->
            <div class="bg-slate-900 rounded-xl border border-slate-800 p-5">
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    🔐 Hidden Login Trigger (Logo Click Count)
                </label>
                <div class="flex items-center gap-3">
                    <input id="set-click-count" type="number" min="3" max="20"
                           value="{{ $settings['logo_click_count'] }}"
                           class="w-24 bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-slate-500 text-sm">clicks (3–20)</span>
                </div>
                <p class="text-slate-500 text-xs mt-1.5">Number of times user must click the logo to open hidden login.</p>
            </div>

            <!-- News API Key -->
            <div class="bg-slate-900 rounded-xl border border-slate-800 p-5">
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    📰 GNews API Key
                    <a href="https://gnews.io" target="_blank" class="text-indigo-400 text-xs ml-1 hover:underline">Get free key →</a>
                </label>
                <input id="set-api-key" type="text" value="{{ $settings['news_api_key'] }}"
                       placeholder="Enter your GNews API key"
                       class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                <p class="text-slate-500 text-xs mt-1.5">Free plan: 100 requests/day. Leave empty for demo news.</p>
            </div>

            <!-- Session Timeout -->
            <div class="bg-slate-900 rounded-xl border border-slate-800 p-5">
                <label class="block text-sm font-medium text-slate-300 mb-2">Session Timeout (minutes)</label>
                <div class="flex items-center gap-3">
                    <input id="set-timeout" type="number" min="1" max="1440"
                           value="{{ $settings['session_timeout'] }}"
                           class="w-24 bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-slate-500 text-sm">minutes</span>
                </div>
            </div>

            <!-- Save Button -->
            <button id="save-settings-btn"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl py-3 text-sm transition shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Settings
            </button>
            <div id="settings-msg" class="hidden text-center text-green-400 text-sm py-1">✅ Settings saved successfully!</div>
        </div>
    </div>
</div>

<!-- ════ MODALS ════════════════════════════════════════════════ -->

<!-- Add/Edit User Modal -->
<div id="user-modal" class="fixed inset-0 modal-overlay z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 rounded-2xl border border-slate-700 w-full max-w-md shadow-2xl">
        <div class="p-6 pb-4 flex items-center justify-between border-b border-slate-800">
            <h3 id="user-modal-title" class="text-white font-semibold">Add New User</h3>
            <button id="close-user-modal" class="text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="user-form" class="p-6 space-y-4">
            <input type="hidden" id="edit-user-id">
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Full Name</label>
                <input id="form-name" type="text"
                       class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="John Doe">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Username</label>
                <input id="form-username" type="text" autocomplete="off"
                       class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                       placeholder="johndoe">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Password <span id="pw-hint" class="text-slate-600">(leave blank to keep current)</span></label>
                <input id="form-password" type="password"
                       class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="••••••••">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Role</label>
                <select id="form-role"
                        class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="user">👤 User</option>
                    <option value="admin">👑 Admin</option>
                </select>
            </div>
            <div id="user-form-error" class="hidden bg-red-900/40 border border-red-700 text-red-300 text-sm rounded-xl px-4 py-2.5"></div>
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl py-3 text-sm transition">
                Save User
            </button>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="reset-pw-modal" class="fixed inset-0 modal-overlay z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 rounded-2xl border border-slate-700 w-full max-w-sm shadow-2xl">
        <div class="p-6 pb-4 flex items-center justify-between border-b border-slate-800">
            <h3 class="text-white font-semibold">Reset Password</h3>
            <button id="close-reset-modal" class="text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-slate-400 text-sm">Resetting password for: <strong id="reset-pw-name" class="text-white"></strong></p>
            <input type="hidden" id="reset-pw-user-id">
            <input id="reset-pw-input" type="password" placeholder="New password (min 6 chars)"
                   class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button id="confirm-reset-pw"
                    class="w-full bg-amber-600 hover:bg-amber-500 text-white font-semibold rounded-xl py-2.5 text-sm transition">
                Reset Password
            </button>
        </div>
    </div>
</div>

<!-- Toast notification -->
<div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
    <div class="bg-slate-800 border border-slate-700 text-white text-sm px-4 py-3 rounded-xl shadow-xl flex items-center gap-2">
        <span id="toast-icon">✅</span>
        <span id="toast-text"></span>
    </div>
</div>

<script>
// ── Tab switching ─────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.remove('hidden');
    });
});

// ── User search ───────────────────────────────────────────────
document.getElementById('user-search').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.user-row').forEach(row => {
        const match = row.dataset.name.includes(q) || row.dataset.username.includes(q);
        row.style.display = match ? '' : 'none';
    });
});

// ── Add User button ───────────────────────────────────────────
document.getElementById('add-user-btn').addEventListener('click', () => {
    openUserModal(null);
});

// ── Edit User buttons ─────────────────────────────────────────
document.querySelectorAll('.edit-user-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const u = JSON.parse(btn.dataset.user);
        openUserModal(u);
    });
});

function openUserModal(user) {
    const isEdit = !!user;
    document.getElementById('user-modal-title').textContent = isEdit ? 'Edit User' : 'Add New User';
    document.getElementById('edit-user-id').value   = user?.id || '';
    document.getElementById('form-name').value      = user?.name || '';
    document.getElementById('form-username').value  = user?.username || '';
    document.getElementById('form-password').value  = '';
    document.getElementById('form-role').value      = user?.role || 'user';
    document.getElementById('pw-hint').classList.toggle('hidden', !isEdit);
    document.getElementById('user-form-error').classList.add('hidden');
    showModal('user-modal');
}

document.getElementById('close-user-modal').addEventListener('click', () => hideModal('user-modal'));

// ── User Form Submit ──────────────────────────────────────────
document.getElementById('user-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id       = document.getElementById('edit-user-id').value;
    const payload  = {
        name:     document.getElementById('form-name').value.trim(),
        username: document.getElementById('form-username').value.trim(),
        role:     document.getElementById('form-role').value,
    };
    const pw = document.getElementById('form-password').value;
    if (pw) payload.password = pw;

    const errBox = document.getElementById('user-form-error');
    errBox.classList.add('hidden');

    try {
        const url    = id ? `${APP.routes.users}/${id}` : APP.routes.users;
        const method = id ? 'PUT' : 'POST';
        const res    = await apiFetch(url, method, payload);
        const data   = await res.json();

        if (data.success) {
            hideModal('user-modal');
            showToast('✅', id ? 'User updated!' : 'User added!');
            setTimeout(() => location.reload(), 1000);
        } else {
            errBox.textContent = data.message || JSON.stringify(data.errors || 'Error occurred');
            errBox.classList.remove('hidden');
        }
    } catch(err) {
        errBox.textContent = 'Server error. Please try again.';
        errBox.classList.remove('hidden');
    }
});

// ── Delete User ───────────────────────────────────────────────
document.querySelectorAll('.delete-user-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm(`Delete user "${btn.dataset.name}"? This cannot be undone.`)) return;
        const res  = await apiFetch(`${APP.routes.users}/${btn.dataset.userId}`, 'DELETE');
        const data = await res.json();
        if (data.success) {
            document.querySelector(`[data-user-id="${btn.dataset.userId}"]`).remove();
            showToast('🗑️', 'User deleted.');
        }
    });
});

// ── Toggle User Status ────────────────────────────────────────
document.querySelectorAll('.toggle-status').forEach(btn => {
    btn.addEventListener('click', async () => {
        const res  = await apiFetch(`${APP.routes.users}/${btn.dataset.userId}/toggle`, 'POST');
        const data = await res.json();
        if (data.success) {
            showToast(data.is_active ? '✅' : '🚫', data.is_active ? 'User activated.' : 'User blocked.');
            setTimeout(() => location.reload(), 800);
        }
    });
});

// ── Reset Password ────────────────────────────────────────────
document.querySelectorAll('.reset-pw-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('reset-pw-user-id').value = btn.dataset.userId;
        document.getElementById('reset-pw-name').textContent = btn.dataset.name;
        document.getElementById('reset-pw-input').value = '';
        showModal('reset-pw-modal');
    });
});

document.getElementById('close-reset-modal').addEventListener('click', () => hideModal('reset-pw-modal'));

document.getElementById('confirm-reset-pw').addEventListener('click', async () => {
    const id = document.getElementById('reset-pw-user-id').value;
    const pw = document.getElementById('reset-pw-input').value;
    if (pw.length < 6) { alert('Password must be at least 6 characters.'); return; }

    const res  = await apiFetch(`${APP.routes.users}/${id}/reset-password`, 'POST', { password: pw });
    const data = await res.json();
    if (data.success) {
        hideModal('reset-pw-modal');
        showToast('🔑', 'Password reset successfully!');
    }
});

// ── Delete Message ────────────────────────────────────────────
document.querySelectorAll('.delete-msg-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this message permanently?')) return;
        const res  = await apiFetch(APP.routes.deleteMessage + btn.dataset.msgId, 'DELETE');
        const data = await res.json();
        if (data.success) {
            document.querySelector(`.msg-row[data-msg-id="${btn.dataset.msgId}"]`).remove();
            showToast('🗑️', 'Message deleted.');
        }
    });
});

// ── Save Settings ─────────────────────────────────────────────
document.getElementById('save-settings-btn').addEventListener('click', async () => {
    const payload = {
        app_name:         document.getElementById('set-app-name').value.trim(),
        logo_click_count: parseInt(document.getElementById('set-click-count').value),
        news_api_key:     document.getElementById('set-api-key').value.trim(),
        session_timeout:  parseInt(document.getElementById('set-timeout').value),
    };
    const res  = await apiFetch(APP.routes.settings, 'POST', payload);
    const data = await res.json();
    if (data.success) {
        const msg = document.getElementById('settings-msg');
        msg.classList.remove('hidden');
        showToast('✅', 'Settings saved!');
        setTimeout(() => msg.classList.add('hidden'), 3000);
    }
});

// ── Logout ────────────────────────────────────────────────────
document.getElementById('logout-btn').addEventListener('click', async () => {
    await apiFetch(APP.routes.logout, 'POST');
    window.location.href = APP.routes.news;
});

// ── Session check ─────────────────────────────────────────────
document.addEventListener('visibilitychange', async () => {
    if (document.visibilityState !== 'visible') return;
    const res  = await fetch(APP.routes.sessionCheck);
    const data = await res.json();
    if (!data.authenticated) window.location.href = APP.routes.news;
});

// ── Helpers ────────────────────────────────────────────────────
function showModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function hideModal(id) {
    const m = document.getElementById(id);
    m.classList.add('hidden');
    m.classList.remove('flex');
}
function apiFetch(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'X-CSRF-TOKEN': APP.csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
    };
    if (body) opts.body = JSON.stringify(body);
    return fetch(url, opts);
}
function showToast(icon, text, duration = 2500) {
    document.getElementById('toast-icon').textContent = icon;
    document.getElementById('toast-text').textContent = text;
    const toast = document.getElementById('toast');
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), duration);
}
</script>
</body>
</html>
