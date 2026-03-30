<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <title>@yield('title', config('app.name', 'NewsHub'))</title>
    <link rel="icon" href="/icons/icon-192.png">
    <!-- Tailwind CDN (swap with compiled in production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#6366f1', dark: '#4f46e5' },
                    },
                    animation: {
                        'fade-in': 'fadeIn .25s ease',
                        'slide-up': 'slideUp .3s ease',
                        'ping-slow': 'ping 2s cubic-bezier(0,0,.2,1) infinite',
                    },
                    keyframes: {
                        fadeIn:  { from: { opacity: 0 },                to: { opacity: 1 } },
                        slideUp: { from: { opacity: 0, transform: 'translateY(20px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
                    }
                }
            }
        }
    </script>
    @stack('head')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <!-- ── NAVBAR ─────────────────────────────────────────────────── -->
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-40 shadow-lg" style="padding-top: env(safe-area-inset-top, 0px);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16 sm:h-20">

            <!-- Logo (7-click trigger) -->
            <div id="app-logo" class="flex items-center gap-2 cursor-pointer select-none" title="{{ config('app.name') }}">
                <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                    </svg>
                </div>
                <span class="font-bold text-white text-lg tracking-tight">{{ config('app.name', 'NewsHub') }}</span>
            </div>

            <!-- Nav Links (desktop) -->
            <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-400">
                <a href="{{ route('news.index') }}" class="hover:text-white transition">Home</a>
                <a href="{{ route('news.index', ['category'=>'technology']) }}" class="hover:text-white transition">Tech</a>
                <a href="{{ route('news.index', ['category'=>'sports']) }}" class="hover:text-white transition">Sports</a>
                <a href="{{ route('news.index', ['category'=>'business']) }}" class="hover:text-white transition">Business</a>
                <a href="{{ route('news.index', ['category'=>'entertainment']) }}" class="hover:text-white transition">Entertainment</a>
            </div>

            <!-- Search + Mobile menu -->
            <div class="flex items-center gap-3">
                <!-- Notification bell (only visible if hidden session exists) -->
                <div id="notif-bell" class="relative hidden cursor-pointer" title="Breaking News Alerts">
                    <svg class="w-5 h-5 text-slate-400 hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span id="notif-badge" class="hidden absolute -top-1 -right-1 min-w-[16px] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 animate-pulse">0</span>
                </div>
                <form method="GET" action="{{ route('news.index') }}" class="hidden sm:block">
                    <input type="text" name="q" placeholder="Search news…"
                           value="{{ request('q') }}"
                           class="bg-slate-800 text-sm text-white placeholder-slate-500 rounded-full px-4 py-1.5 w-44 focus:w-56 transition-all outline-none focus:ring-2 focus:ring-indigo-500 border border-slate-700">
                </form>
                <!-- Mobile hamburger -->
                <button id="mobile-menu-btn" class="md:hidden text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-800 bg-slate-900 px-4 py-3 space-y-2 text-sm font-medium text-slate-300">
            <a href="{{ route('news.index') }}" class="block py-1.5 hover:text-white">Home</a>
            <a href="{{ route('news.index', ['category'=>'technology']) }}" class="block py-1.5 hover:text-white">Technology</a>
            <a href="{{ route('news.index', ['category'=>'sports']) }}" class="block py-1.5 hover:text-white">Sports</a>
            <a href="{{ route('news.index', ['category'=>'business']) }}" class="block py-1.5 hover:text-white">Business</a>
            <form method="GET" action="{{ route('news.index') }}" class="mt-2">
                <input type="text" name="q" placeholder="Search news…"
                       value="{{ request('q') }}"
                       class="w-full bg-slate-800 text-sm text-white placeholder-slate-500 rounded-full px-4 py-2 outline-none border border-slate-700">
            </form>
        </div>
    </nav>

    <!-- Breaking News Toast (notification toast) -->
    <div id="news-toast" class="fixed top-16 right-4 z-50 max-w-sm hidden animate-slide-up">
        <div class="bg-gradient-to-r from-red-900/90 to-orange-900/90 backdrop-blur-md border border-red-500/30 rounded-2xl p-3 shadow-2xl shadow-red-500/20 cursor-pointer" onclick="this.parentElement.classList.add('hidden')">
            <div class="flex items-center gap-2">
                <span class="text-lg">🚨</span>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-bold">Breaking News Alert</p>
                    <p id="toast-text" class="text-red-200 text-[11px] truncate">New updates available — tap to read</p>
                </div>
                <span class="text-red-300 text-xs">✕</span>
            </div>
        </div>
    </div>

    <!-- ── PAGE CONTENT ───────────────────────────────────────────── -->
    <main class="@yield('main-class', 'max-w-7xl mx-auto px-4 sm:px-6 py-6')">
        @yield('content')
    </main>

    <!-- ── HIDDEN LOGIN MODAL ─────────────────────────────────────── -->
    <div id="hidden-login-modal"
         class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-fade-in">
        <div class="bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm border border-slate-700 animate-slide-up">

            <!-- Modal Header -->
            <div class="p-6 pb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white font-semibold text-base">Private Access</h2>
                        <p class="text-slate-500 text-xs">Enter your credentials</p>
                    </div>
                </div>
                <button id="close-login-modal" class="text-slate-500 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Error alert -->
            <div id="login-error" class="hidden mx-6 mb-3 bg-red-900/50 border border-red-700 text-red-300 text-sm rounded-lg px-4 py-2.5"></div>

            <!-- Form -->
            <form id="hidden-login-form" class="px-6 pb-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Username</label>
                    <input id="login-username" type="text" autocomplete="off" autocorrect="off"
                           class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition placeholder-slate-600"
                           placeholder="Enter username">
                </div>
                <div class="relative">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Password</label>
                    <input id="login-password" type="password" autocomplete="current-password"
                           class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition placeholder-slate-600"
                           placeholder="Enter password">
                    <button type="button" id="toggle-password"
                            class="absolute right-3 bottom-3 text-slate-500 hover:text-slate-300">
                        <svg class="w-4 h-4" id="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <button type="submit" id="login-btn"
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl py-3 text-sm transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="login-btn-text">Sign In</span>
                    <svg id="login-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- ── SESSION EXPIRED MODAL ──────────────────────────────────── -->
    <div id="session-expired-modal"
         class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl shadow-2xl w-full max-w-xs border border-slate-700 p-6 text-center animate-slide-up">
            <div class="w-14 h-14 bg-amber-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-white font-semibold mb-1">Session Expired</h3>
            <p class="text-slate-400 text-sm mb-5">Please log in again to continue.</p>
            <button id="relogin-btn"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl py-2.5 text-sm transition">
                Log In Again
            </button>
        </div>
    </div>

    <!-- ── SCRIPTS ────────────────────────────────────────────────── -->
    <script>
    // ── Config injected from server ──────────────────────────────
    window.APP = {
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        routes: {
            login:        '{{ route("hidden.login") }}',
            logout:       '{{ route("hidden.logout") }}',
            sessionCheck: '{{ route("hidden.session-check") }}',
            clickCount:   '{{ route("hidden.click-count") }}',
            chat:         '{{ route("chat.index") }}',
            admin:        '{{ route("admin.dashboard") }}',
        },
    };

    // ── Mobile menu ──────────────────────────────────────────────
    document.getElementById('mobile-menu-btn').addEventListener('click', () => {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });

    // ── Logo click counter (hidden trigger) ──────────────────────
    (() => {
        let clicks = 0, timer = null, requiredClicks = 7;

        // Fetch required clicks from server (admin can change this)
        fetch(APP.routes.clickCount)
            .then(r => r.json())
            .then(d => { requiredClicks = d.count; })
            .catch(() => {});

        document.getElementById('app-logo').addEventListener('click', () => {
            clicks++;
            clearTimeout(timer);

            if (clicks >= requiredClicks) {
                clicks = 0;
                openLoginModal();
                return;
            }

            // Reset counter if too slow (3 seconds)
            timer = setTimeout(() => { clicks = 0; }, 3000);
        });
    })();

    // ── Login Modal ──────────────────────────────────────────────
    const modal    = document.getElementById('hidden-login-modal');
    const errBox   = document.getElementById('login-error');
    const loginBtn = document.getElementById('login-btn');

    function openLoginModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('login-username').focus();
        errBox.classList.add('hidden');
    }

    function closeLoginModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('hidden-login-form').reset();
    }

    document.getElementById('close-login-modal').addEventListener('click', closeLoginModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeLoginModal(); });

    // Toggle password visibility
    document.getElementById('toggle-password').addEventListener('click', function() {
        const inp = document.getElementById('login-password');
        inp.type = inp.type === 'password' ? 'text' : 'password';
    });

    // Handle login form submit
    document.getElementById('hidden-login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;

        if (!username || !password) {
            showLoginError('Please fill in all fields.');
            return;
        }

        loginBtn.disabled = true;
        document.getElementById('login-btn-text').textContent = 'Signing in…';
        document.getElementById('login-spinner').classList.remove('hidden');
        errBox.classList.add('hidden');

        try {
            const res = await fetch(APP.routes.login, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrfToken },
                body: JSON.stringify({ username, password }),
            });
            const data = await res.json();

            if (data.success) {
                closeLoginModal();
                window.location.href = data.redirect;
            } else {
                showLoginError(data.error || 'Invalid credentials.');
            }
        } catch (err) {
            showLoginError('Connection error. Please try again.');
        } finally {
            loginBtn.disabled = false;
            document.getElementById('login-btn-text').textContent = 'Sign In';
            document.getElementById('login-spinner').classList.add('hidden');
        }
    });

    function showLoginError(msg) {
        errBox.textContent = msg;
        errBox.classList.remove('hidden');
    }

    // ── Session check on visibility change ──────────────────────
    // When user returns to app after screen off / tab switch
    document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState !== 'visible') return;
        if (!window.__hiddenModeActive) return;

        try {
            const res  = await fetch(APP.routes.sessionCheck);
            const data = await res.json();
            if (!data.authenticated) {
                showSessionExpired();
            }
        } catch (e) {}
    });

    // Session expired modal
    const expiredModal = document.getElementById('session-expired-modal');

    function showSessionExpired() {
        window.__hiddenModeActive = false;
        expiredModal.classList.remove('hidden');
        expiredModal.classList.add('flex');
    }

    document.getElementById('relogin-btn').addEventListener('click', () => {
        expiredModal.classList.add('hidden');
        expiredModal.classList.remove('flex');
        openLoginModal();
    });

    // ── PWA Service Worker ────────────────────────────────────────
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    // ── News Page Notification System (for logged-in hidden users) ──
    (() => {
        let lastUnread = 0, pollActive = false;
        const bell = document.getElementById('notif-bell');
        const badge = document.getElementById('notif-badge');
        const toast = document.getElementById('news-toast');
        const toastText = document.getElementById('toast-text');

        // News headlines for disguised notifications
        const newsHeadlines = [
            'Markets surge 3% on positive economic data',
            'New tech breakthrough announced in AI sector',
            'Weather alert: Heavy rains expected this week',
            'Sports update: Cricket finals approaching',
            'Political developments: New policy discussions',
            'Entertainment: Bollywood box office records',
            'Health advisory: New wellness guidelines released',
            'Economy: Stock market hits all-time high',
            'Science: Major discovery in space exploration',
            'World: International summit begins today',
        ];

        function randomHeadline() {
            return newsHeadlines[Math.floor(Math.random() * newsHeadlines.length)];
        }

        async function checkUnread() {
            try {
                const r = await fetch('/chat/unread-total');
                const d = await r.json();
                if (d.count > 0) {
                    bell.classList.remove('hidden');
                    badge.textContent = d.count;
                    badge.classList.remove('hidden');

                    // New messages arrived since last check
                    if (d.count > lastUnread && lastUnread >= 0) {
                        showNewsToast();
                        showBrowserNotification();
                    }
                } else {
                    badge.classList.add('hidden');
                }
                lastUnread = d.count;
            } catch(e) {}
        }

        function showNewsToast() {
            toastText.textContent = randomHeadline();
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 6000);
        }

        function showBrowserNotification() {
            if (!('Notification' in window)) return;
            if (Notification.permission === 'granted') {
                const n = new Notification('🚨 Breaking News Alert', {
                    body: randomHeadline(),
                    icon: '/icons/icon-192.png',
                    badge: '/icons/icon-192.png',
                    tag: 'news-' + Date.now(),
                    vibrate: [100, 50, 100],
                    silent: false,
                });
                n.onclick = () => { window.focus(); n.close(); };
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission();
            }
        }

        // Start polling if user has a hidden session
        async function initNotifPolling() {
            try {
                const r = await fetch('/chat/unread-total');
                const d = await r.json();
                // If endpoint returns a count (even 0), user is logged in
                if (d.count !== undefined) {
                    bell.classList.remove('hidden');
                    lastUnread = d.count;
                    if (d.count > 0) {
                        badge.textContent = d.count;
                        badge.classList.remove('hidden');
                    }
                    pollActive = true;
                    setInterval(checkUnread, 60000); // Every 60s instead of 10s
                }
            } catch(e) {}
        }

        // Request notification permission on bell click
        bell?.addEventListener('click', () => {
            if ('Notification' in window && Notification.permission !== 'granted') {
                Notification.requestPermission();
            }
        });

        initNotifPolling();
    })();
    </script>

    @stack('scripts')
</body>
</html>
