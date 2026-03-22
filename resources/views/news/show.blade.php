<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#030712">
    <title>{{ $article['title'] }} - {{ config('app.name','NewsHub') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', '"Noto Sans Devanagari"', 'sans-serif'] },
                    colors: { brand: { 500: '#f43f5e', 600: '#e11d48' } },
                }
            }
        }
    </script>
    <style>
        body { background-color: #030712; color: #f8fafc; overflow-x: hidden; }
        .glass-nav {
            background: rgba(3, 7, 18, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .prose-custom p { margin-bottom: 1.5em; line-height: 1.8; color: #cbd5e1; font-size: 1.125rem; }
        .prose-custom p:first-of-type::first-letter { font-size: 3.5rem; float: left; margin-right: 0.15em; line-height: 1; font-weight: 800; color: #f43f5e; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-brand-500 selection:text-white relative">

    <!-- Ambient background glows -->
    <div class="fixed top-0 inset-x-0 h-[500px] bg-gradient-to-b from-brand-600/5 to-transparent pointer-events-none -z-10 mix-blend-screen opacity-50"></div>

    <!-- ── NAVIGATION ── -->
    <header class="glass-nav fixed top-0 w-full z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16 sm:h-20">
                <a href="{{ route('news.index') }}" class="flex items-center gap-2 group text-slate-300 hover:text-white transition-colors">
                    <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="font-bold tracking-wide">Back to News</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ── MAIN CONTENT ── -->
    <main class="flex-1 pt-24 sm:pt-32 pb-16 w-full z-10 relative">
        <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Category & Time -->
            <div class="flex items-center gap-3 mb-6">
                <span class="bg-brand-500 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">{{ $article['source']['name'] ?? 'Breaking News' }}</span>
                <span class="text-slate-400 text-sm font-medium flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ \Carbon\Carbon::parse($article['publishedAt'])->format('F j, Y - g:i A') }}
                </span>
            </div>

            <!-- Title -->
            <h1 class="text-white text-3xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.15] tracking-tight mb-8 drop-shadow-md">
                {{ $article['title'] }}
            </h1>
            
            <!-- Main Media / Image -->
            <div class="w-full aspect-[16/9] bg-slate-900 rounded-[2rem] overflow-hidden border border-slate-800 mb-10 shadow-2xl relative">
                @if(!empty($article['image']))
                <img src="{{ $article['image'] }}" class="absolute inset-0 w-full h-full object-cover" alt="Article Media">
                @else
                <div class="absolute inset-0 flex items-center justify-center bg-slate-800 text-slate-500">
                    <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-[#030712] via-transparent to-transparent opacity-60"></div>
            </div>

            <!-- Reading Meta -->
            <div class="flex items-center justify-between py-4 border-y border-slate-800/80 mb-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-slate-600 flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm">Staff Reporter</p>
                        <p class="text-slate-400 text-xs">NewsHub Exclusive</p>
                    </div>
                </div>
                <!-- Social Share Icons (Dummies) -->
                <div class="flex gap-2">
                    <button class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-500/50 transition-colors"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></button>
                    <button class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 hover:text-blue-500 hover:border-blue-500/50 transition-colors"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg></button>
                </div>
            </div>

            <!-- Authentic Story Body -->
            <div class="prose-custom max-w-none">
                <p>
                    {{ $article['description'] }}
                </p>

                <!-- Embedded Source Article iframe -->
                <div class="my-6 w-full bg-slate-900 rounded-[2rem] overflow-hidden border border-slate-700/50 shadow-2xl relative" style="height: 80vh;">
                    
                    <!-- Sandboxed Embed -->
                    <iframe src="{{ $article['url'] }}" 
                            class="w-full h-full border-0 bg-white" 
                            title="Full Article" 
                            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                            referrerpolicy="no-referrer">
                    </iframe>
                </div>

                <!-- Fallback block for X-Frame-Options blocked sites -->
                <div class="mb-10 p-6 sm:p-8 bg-slate-800/30 rounded-3xl border border-slate-700/30 text-center flex flex-col items-center justify-center">
                    <h3 class="text-white font-bold text-xl mb-2">Continue Reading</h3>
                    <p class="text-slate-400 mb-6 font-medium text-sm sm:text-base max-w-2xl">Some publishers block embedded reading. If the article fails to load inside the frame above, you can securely read the full coverage directly on their official website.</p>
                    <a href="{{ $article['url'] }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-full inline-flex items-center justify-center gap-3 transition-all shadow-[0_0_20px_rgba(244,63,94,0.3)] hover:shadow-[0_0_40px_rgba(244,63,94,0.5)] transform hover:-translate-y-1 duration-300">
                        <span>Read Full Article Here</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>

            <!-- Authenticity Footer / Disclaimer -->
            <div class="mt-16 pt-8 border-t border-slate-800/80 bg-slate-900/30 rounded-2xl p-6 text-sm text-slate-400">
                <p class="mb-2"><strong class="text-slate-300">Editor's Note:</strong> This coverage is syndicated from verified partners and processed through NewsHub's automated journalism aggregation engine. The multimedia content may include synthesized assets for illustrative representation.</p>
                <p>Copyright &copy; {{ date('Y') }} NewsHub Inc. Unauthorized replication is prohibited.</p>
            </div>
        </article>
    </main>

    <!-- ── FOOTER ── -->
    <footer class="mt-auto border-t border-slate-800/50 bg-[#030712] relative z-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-brand-500 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <span class="text-white font-extrabold text-xl">{{ config('app.name','NewsHub') }}</span>
                </div>
                <div class="flex gap-4 text-sm font-medium text-slate-500">
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
