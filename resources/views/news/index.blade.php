<!DOCTYPE html>
<html lang="{{ $lang === 'hindi' ? 'hi' : 'en' }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#030712">
    <title>{{ config('app.name','NewsHub') }} - {{ ucfirst($category) }} News</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Noto Sans Devanagari"', 'sans-serif'],
                    },
                    colors: {
                        brand: { 50: '#fff1f2', 100: '#ffe4e6', 500: '#f43f5e', 600: '#e11d48', 950: '#4c0519' },
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'marquee': 'marquee 40s linear infinite',
                    },
                    keyframes: {
                        marquee: { '0%': { transform: 'translateX(100vw)' }, '100%': { transform: 'translateX(-100%)' } }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #030712; color: #f8fafc; overflow-x: hidden; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        
        .glass-nav {
            background: rgba(3, 7, 18, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            border-color: rgba(244, 63, 94, 0.3);
        }
        
        .img-gradient::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(3, 7, 18, 0.95) 0%, rgba(3, 7, 18, 0.4) 50%, transparent 100%);
        }
        
        #hidden-login-modal {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-brand-500 selection:text-white relative">

    <!-- Ambient background glows -->
    <div class="fixed top-0 inset-x-0 h-[500px] bg-gradient-to-b from-brand-600/10 to-transparent pointer-events-none -z-10 mix-blend-screen opacity-50"></div>
    <div class="fixed top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-brand-600/10 blur-[120px] pointer-events-none -z-20"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[120px] pointer-events-none -z-20"></div>

    <!-- ── NAVIGATION ── -->
    <header class="glass-nav fixed top-0 w-full z-40 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo -->
                <div id="app-logo" class="flex items-center gap-3 cursor-pointer group select-none shrink-0" title="NewsHub">
                    <div class="relative w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-brand-500 to-rose-600 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-500/20 group-hover:shadow-brand-500/40 transition-all duration-300 group-hover:scale-105">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-[#030712] animate-pulse"></div>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-white font-extrabold text-xl tracking-tight leading-none group-hover:text-brand-50 transition-colors">{{ config('app.name','NewsHub') }}</h1>
                        <p class="text-[10px] font-bold text-brand-500 tracking-[0.2em] uppercase mt-1">Live Updates</p>
                    </div>
                </div>

                <!-- Desktop Categories -->
                <nav class="hidden lg:flex items-center gap-1.5 ml-8">
                    @foreach(['general'=>'Global','technology'=>'Tech','business'=>'Markets','sports'=>'Sports','entertainment'=>'Entertainment','science'=>'Science'] as $cat => $label)
                    <a href="{{ route('news.index',['category'=>$cat,'lang'=>$lang]) }}"
                       class="px-4 py-2 rounded-full text-sm font-semibold transition-all duration-300
                       {{ $category === $cat 
                           ? 'bg-white text-[#030712] shadow-md shadow-white/10' 
                           : 'text-slate-400 hover:text-white hover:bg-white/10' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center gap-3 ml-auto">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('news.index') }}" class="hidden md:block relative group">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <input type="hidden" name="category" value="{{ $category }}">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-500 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Search news..."
                               class="bg-slate-900/50 border border-slate-800 text-white text-sm rounded-full pl-10 pr-4 py-2 w-48 focus:w-64 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500/50 placeholder-slate-500">
                    </form>

                    <!-- Language Switcher -->
                    <a href="{{ route('news.index',['category'=>$category,'lang'=>$lang==='hindi'?'english':'hindi','q'=>$search]) }}"
                       class="flex items-center gap-2 bg-slate-800/50 hover:bg-slate-700/80 border border-slate-700/50 text-white px-3 sm:px-4 py-2 rounded-full text-sm font-semibold transition-colors shrink-0">
                        <span class="text-lg leading-none">{{ $lang==='hindi' ? '🇮🇳' : '🇬🇧' }}</span>
                        <span class="hidden sm:block text-slate-300">{{ $lang==='hindi' ? 'हिंदी' : 'EN' }}</span>
                    </a>
                </div>
            </div>

            <!-- Mobile Categories (Scrollable) -->
            <div class="lg:hidden flex gap-2 overflow-x-auto hide-scroll pb-4 pt-1 items-center">
                @foreach(['general'=>'Global','technology'=>'Tech','business'=>'Markets','sports'=>'Sports','entertainment'=>'Entertainment'] as $cat => $label)
                <a href="{{ route('news.index',['category'=>$cat,'lang'=>$lang]) }}"
                   class="shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold transition-all duration-300 border
                   {{ $category === $cat 
                       ? 'bg-white text-[#030712] border-transparent shadow-lg shadow-white/10' 
                       : 'bg-slate-900/50 text-slate-400 border-slate-800 hover:text-white hover:bg-slate-800' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
    </header>

    <!-- ── CURRENT DATE & TIME BAR ── -->
    <div class="mt-16 sm:mt-20 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md z-30 relative px-4 sm:px-6 lg:px-8 py-2.5 flex justify-between items-center text-xs font-semibold text-slate-400 shadow-sm">
        <div id="live-datetime" class="flex flex-wrap items-center gap-2 sm:gap-3">
            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span id="dt-day" class="text-white"></span>
            <span class="w-1 h-1 bg-slate-700 rounded-full"></span>
            <span id="dt-date"></span>
            <span class="w-1 h-1 bg-slate-700 rounded-full hidden sm:inline-block"></span>
            <span id="dt-time" class="text-brand-400 hidden sm:inline-block"></span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            <span class="hidden sm:inline-block">Live Edition • </span>{{ ucfirst($category) }}
        </div>
    </div>

    <!-- ── LIVEWIRE NEWS FEED COMPONENT ── -->
    <livewire:news-feed />

    <!-- ── FOOTER ── -->
    <footer class="mt-auto border-t border-slate-800/50 bg-[#030712] relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-brand-500 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <span class="text-white font-extrabold text-xl tracking-tight">{{ config('app.name','NewsHub') }}</span>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-slate-400 text-sm font-medium mb-2">Powering real-time journalism</p>
                    <p class="text-slate-600 text-xs">Sources: NDTV • Aaj Tak • Times of India • ABP News • The Hindu</p>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-800/50 text-center flex flex-col sm:flex-row items-center justify-between text-slate-500 text-xs font-medium">
                <p>&copy; {{ date('Y') }} {{ config('app.name','NewsHub') }} Inc. All rights reserved.</p>
                <div class="flex gap-4 mt-4 sm:mt-0">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ── HIDDEN LOGIN MODAL ── -->
    <div id="hidden-login-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 opacity-0 transition-opacity duration-300">
        <div class="bg-[#030712]/95 rounded-[2rem] shadow-2xl shadow-brand-500/10 w-full max-w-sm border border-slate-800 transform scale-95 transition-transform duration-300 overflow-hidden relative" id="hidden-modal-content">
            <!-- Modal glow -->
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-brand-600 via-purple-600 to-blue-600 opacity-50"></div>
            
            <div class="p-8 pb-6">
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-2xl flex items-center justify-center shadow-lg relative">
                        <svg class="w-7 h-7 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <div class="absolute inset-0 bg-brand-500/20 blur-xl rounded-2xl"></div>
                    </div>
                </div>
                <div class="text-center mb-8">
                    <h2 class="text-white font-extrabold text-2xl tracking-tight">System Access</h2>
                    <p class="text-slate-400 text-sm mt-1">Authorized personnel only</p>
                </div>
                
                <div id="login-error" class="hidden text-center mb-4 bg-brand-950/40 border border-brand-900 text-brand-400 text-sm rounded-xl py-3 px-4 font-medium animate-pulse"></div>
                
                <form id="hidden-login-form" class="space-y-4">
                    <div>
                        <input id="login-username" type="text" autocomplete="off" spellcheck="false"
                               class="w-full bg-slate-900 border border-slate-700 hover:border-slate-600 focus:border-brand-500 text-white rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-brand-500/10 transition-all placeholder-slate-500"
                               placeholder="Admin ID">
                    </div>
                    <div class="relative">
                        <input id="login-password" type="password" autocomplete="current-password"
                               class="w-full bg-slate-900 border border-slate-700 hover:border-slate-600 focus:border-brand-500 text-white rounded-2xl px-5 py-4 pr-12 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-brand-500/10 transition-all placeholder-slate-500"
                               placeholder="Passcode">
                        <button type="button" id="toggle-pw" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <button type="submit" id="login-btn"
                            class="w-full relative overflow-hidden bg-white text-[#030712] font-bold rounded-2xl py-4 transition-all hover:bg-slate-200 shadow-[0_0_20px_rgba(255,255,255,0.1)] hover:shadow-[0_0_25px_rgba(255,255,255,0.2)] disabled:opacity-50 disabled:cursor-not-allowed group">
                        <span id="login-btn-text" class="relative z-10 flex items-center justify-center gap-2">
                            Authenticate
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                        <svg id="login-spinner" class="hidden absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </form>
            </div>
            
            <button id="close-login-modal" class="absolute top-5 right-5 w-8 h-8 flex items-center justify-center rounded-full bg-slate-800/50 text-slate-400 hover:text-white hover:bg-slate-700 transition lg:hidden sm:flex">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <script>
    // Live Date Time updater
    function updateDateTime() {
        const now = new Date();
        const lang = '{{ $lang === "hindi" ? "hi-IN" : "en-US" }}';
        
        const dayEl = document.getElementById('dt-day');
        const dateEl = document.getElementById('dt-date');
        const timeEl = document.getElementById('dt-time');
        
        if (dayEl) dayEl.textContent = now.toLocaleDateString(lang, { weekday: 'long' });
        if (dateEl) dateEl.textContent = now.toLocaleDateString(lang, { year: 'numeric', month: 'long', day: 'numeric' });
        if (timeEl) timeEl.textContent = now.toLocaleTimeString(lang, { hour: '2-digit', minute: '2-digit' });
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);

    window.APP = {
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        routes: {
            login:      '{{ route("hidden.login") }}',
            clickCount: '{{ route("hidden.click-count") }}',
        },
    };

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const nav = document.querySelector('.glass-nav');
        if (window.scrollY > 20) {
            nav.classList.add('shadow-xl', 'bg-[#030712]/85');
        } else {
            nav.classList.remove('shadow-xl', 'bg-[#030712]/85');
        }
    });

    // 7-click secret trigger
    (() => {
        let clicks = 0, timer = null, needed = 7;
        fetch(APP.routes.clickCount).then(r=>r.json()).then(d=>{needed=d.count;}).catch(()=>{});
        document.getElementById('app-logo').addEventListener('click', () => {
            clicks++;
            clearTimeout(timer);
            if (clicks >= needed) { clicks = 0; openModal(); return; }
            timer = setTimeout(() => { clicks = 0; }, 3000);
        });
    })();

    // Modal Logic
    const modal = document.getElementById('hidden-login-modal');
    const modalContent = document.getElementById('hidden-modal-content');
    const errBox = document.getElementById('login-error');

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Small delay for animation
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
            document.getElementById('login-username').focus();
        }, 10);
        errBox.classList.add('hidden');
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('hidden-login-form').reset();
        }, 300);
    }

    document.getElementById('close-login-modal').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    
    document.getElementById('toggle-pw').addEventListener('click', () => {
        const p = document.getElementById('login-password');
        p.type = p.type === 'password' ? 'text' : 'password';
    });

    document.getElementById('hidden-login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const u = document.getElementById('login-username').value.trim();
        const p = document.getElementById('login-password').value;
        if(!u || !p) { showErr('Credentials strictly required.'); return; }
        
        const btn = document.getElementById('login-btn');
        const btnText = document.getElementById('login-btn-text');
        const spinner = document.getElementById('login-spinner');
        
        btn.disabled = true;
        btnText.classList.add('opacity-0');
        spinner.classList.remove('hidden');
        errBox.classList.add('hidden');
        
        try{
            const res = await fetch(APP.routes.login, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrfToken },
                body: JSON.stringify({username:u, password:p})
            });
            const data = await res.json();
            if (data.success) {
                btn.classList.replace('bg-white', 'bg-green-500');
                btn.classList.add('text-white');
                setTimeout(() => { window.location.href = data.redirect; }, 500);
            } else {
                showErr(data.error || 'Access Denied.');
                resetBtn();
            }
        }catch{
            showErr('Network error. Unable to authenticate.');
            resetBtn();
        }
        
        function resetBtn() {
            btn.disabled = false;
            btnText.classList.remove('opacity-0');
            spinner.classList.add('hidden');
        }
    });
    
    function showErr(m) { 
        errBox.textContent = m; 
        errBox.classList.remove('hidden'); 
    }
    
    if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(()=>{});
    </script>
</body>
</html>
