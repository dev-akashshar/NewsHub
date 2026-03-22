<div class="w-full relative">
    <!-- ── ADVANCED FILTERS BAR ── -->
    <div class="bg-slate-950/90 backdrop-blur-md border-b border-slate-800 z-20 relative w-full shadow-lg overflow-x-auto no-scrollbar hidden sm:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
            <div class="flex items-center gap-3 sm:gap-4 text-xs font-medium min-w-max">
                
                <div class="flex items-center gap-2 mr-6 text-slate-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Feed Refinement
                    
                    <div wire:loading class="ml-2">
                        <svg class="w-4 h-4 text-brand-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Sort By -->
                <select wire:model.live="sortBy" class="shrink-0 bg-slate-900 border border-slate-700 text-slate-300 text-xs rounded-full px-3 py-1.5 focus:ring-brand-500 focus:border-brand-500 outline-none hover:bg-slate-800 transition-colors cursor-pointer">
                    <option value="">Sort: Relevant</option>
                    <option value="publishedAt">Sort: Latest</option>
                    <option value="popularity">Sort: Popular</option>
                </select>
                
                <!-- Timeframe -->
                <select wire:model.live="from" class="shrink-0 bg-slate-900 border border-slate-700 text-slate-300 text-xs rounded-full px-3 py-1.5 focus:ring-brand-500 focus:border-brand-500 outline-none hover:bg-slate-800 transition-colors cursor-pointer">
                    <option value="">Time: Last 3 Days</option>
                    <option value="today">Time: Today</option>
                    <option value="week">Time: Past Week</option>
                    <option value="month">Time: Past Month</option>
                </select>

                <!-- Country -->
                <select wire:model.live="country" class="shrink-0 bg-slate-900 border border-slate-700 text-slate-300 text-xs rounded-full px-3 py-1.5 focus:ring-brand-500 focus:border-brand-500 outline-none hover:bg-slate-800 transition-colors cursor-pointer">
                    <option value="">Region: Global</option>
                    <option value="in">Region: India</option>
                    <option value="us">Region: United States</option>
                    <option value="gb">Region: United Kingdom</option>
                </select>

                <!-- Source  -->
                <select wire:model.live="source" class="shrink-0 bg-slate-900 border border-slate-700 text-slate-300 text-xs rounded-full px-3 py-1.5 focus:ring-brand-500 focus:border-brand-500 outline-none hover:bg-slate-800 transition-colors cursor-pointer">
                    <option value="">Source: Any</option>
                    <option value="bbc-news">BBC News</option>
                    <option value="cnn">CNN</option>
                    <option value="the-times-of-india">Times of India</option>
                    <option value="the-hindu">The Hindu</option>
                    <option value="techcrunch">TechCrunch</option>
                </select>

                @if(!empty($country) || !empty($source) || !empty($from) || !empty($sortBy))
                <button wire:click="$set('country', ''); $set('source', ''); $set('from', ''); $set('sortBy', '');" class="text-brand-500 hover:text-brand-400 underline decoration-brand-500/50 underline-offset-2 ml-2 font-bold shrink-0 transition-colors cursor-pointer">Clear Filters</button>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Mobile Filter Bar (Horizontal Chips) -->
    <div class="sm:hidden bg-[#030712]/95 backdrop-blur-md border-b border-slate-800/80 z-20 relative w-full shadow-md">
        <div class="flex items-center gap-2.5 overflow-x-auto hide-scroll px-4 py-2.5">
            <div class="flex items-center gap-1.5 shrink-0 text-slate-500 font-bold text-[10px] uppercase tracking-wider mr-1 pr-3 border-r border-slate-800/80">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            </div>
            <select wire:model.live="sortBy" class="shrink-0 bg-slate-900 border border-slate-700/80 text-slate-300 rounded-full pl-3 pr-7 py-1.5 text-[11px] font-semibold focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none cursor-pointer appearance-none" style="background-image:url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394a3b8%22 stroke-width=%222%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 6px center;background-size:12px;">
                <option value="">Sort</option>
                <option value="publishedAt">Latest</option>
                <option value="popularity">Popular</option>
            </select>
            <select wire:model.live="from" class="shrink-0 bg-slate-900 border border-slate-700/80 text-slate-300 rounded-full pl-3 pr-7 py-1.5 text-[11px] font-semibold focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none cursor-pointer appearance-none" style="background-image:url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394a3b8%22 stroke-width=%222%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 6px center;background-size:12px;">
                <option value="">Time</option>
                <option value="today">Today</option>
                <option value="week">Week</option>
                <option value="month">Month</option>
            </select>
            <select wire:model.live="country" class="shrink-0 bg-slate-900 border border-slate-700/80 text-slate-300 rounded-full pl-3 pr-7 py-1.5 text-[11px] font-semibold focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none cursor-pointer appearance-none" style="background-image:url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394a3b8%22 stroke-width=%222%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 6px center;background-size:12px;">
                <option value="">Region</option>
                <option value="in">India</option>
                <option value="us">US</option>
                <option value="gb">UK</option>
            </select>
            <select wire:model.live="source" class="shrink-0 bg-slate-900 border border-slate-700/80 text-slate-300 rounded-full pl-3 pr-7 py-1.5 text-[11px] font-semibold focus:ring-1 focus:ring-brand-500 focus:border-brand-500 outline-none cursor-pointer appearance-none" style="background-image:url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2394a3b8%22 stroke-width=%222%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 6px center;background-size:12px;">
                <option value="">Source</option>
                <option value="bbc-news">BBC</option>
                <option value="cnn">CNN</option>
                <option value="the-times-of-india">TOI</option>
            </select>
            @if(!empty($country) || !empty($source) || !empty($from) || !empty($sortBy))
            <button wire:click="$set('country', ''); $set('source', ''); $set('from', ''); $set('sortBy', '');" class="shrink-0 rounded-full w-6 h-6 bg-brand-500/20 text-brand-400 flex items-center justify-center hover:bg-brand-500 hover:text-white transition-colors cursor-pointer" title="Clear">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            @endif
        </div>
        <div wire:loading class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-500 pointer-events-none">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" class="opacity-75"></path></svg>
        </div>
    </div>

    <!-- ── BREAKING TICKER ── -->
    @if(count($news) > 3)
    <div class="w-full bg-brand-600/10 border-b border-brand-500/20 overflow-hidden relative flex text-sm shadow-inner shadow-brand-900/20">
        <div class="bg-brand-600 px-4 py-2 text-white font-bold uppercase tracking-widest shrink-0 flex items-center gap-2 z-10 shadow-lg shadow-brand-600/30">
            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
            Breaking
        </div>
        <div class="flex-1 flex items-center overflow-hidden whitespace-nowrap">
            <div class="animate-marquee inline-block font-medium text-slate-300">
                @foreach(array_slice($news,0,5) as $n)
                    <span class="mx-4 text-brand-500">◆</span> {{ $n['title'] }}
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="pt-4"></div>
    @endif

    <!-- ── MAIN CONTENT ── -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full z-10 relative" wire:loading.class="opacity-50 pointer-events-none transition-opacity duration-300">
        
        <!-- ── ERROR / EMPTY STATES ── -->
    @if(empty($news))
        <div class="mt-8 mb-16 max-w-2xl mx-auto text-center py-16 px-6 bg-slate-900/40 rounded-[2rem] border border-slate-800 backdrop-blur-sm">
            @if($search || $country || $source || $lang !== 'english')
                <!-- "No Match Found" state -->
                <div class="w-24 h-24 bg-slate-900 rounded-3xl border border-slate-800 flex items-center justify-center mb-6 shadow-2xl relative mx-auto">
                    <div class="absolute inset-0 bg-brand-500/20 blur-xl rounded-full"></div>
                    <svg class="w-10 h-10 text-slate-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3 class="text-white text-2xl font-bold mb-3 tracking-tight">No match found for your filters</h3>
                <p class="text-slate-400 text-base mb-8 leading-relaxed">Try adjusting your search keywords, clearing your filters, or broadening the timeframe.</p>
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <button wire:click="$set('search', ''); $set('country', ''); $set('source', ''); $set('from', ''); $set('sortBy', ''); $set('lang', 'english');" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-full text-sm font-medium transition-colors cursor-pointer">Clear Filters</button>
                    <button wire:click="$refresh" class="flex items-center gap-2 bg-white hover:bg-gray-100 text-[#030712] px-6 py-3 rounded-full font-bold transition-all shadow-lg hover:shadow-white/20 hover:-translate-y-0.5 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Retry Request
                    </button>
                </div>
            @else
                <!-- "News Feed Unavailable" state -->
                <div class="w-24 h-24 bg-slate-900 rounded-3xl border border-slate-800 flex items-center justify-center mb-6 shadow-2xl relative mx-auto">
                    <div class="absolute inset-0 bg-brand-500/20 blur-xl rounded-full"></div>
                    <svg class="w-10 h-10 text-slate-500 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3 class="text-white text-2xl font-bold mb-3 tracking-tight">News Feed Unavailable</h3>
                <p class="text-slate-400 text-base mb-8 leading-relaxed">Our systems are temporarily unable to fetch articles from the live APIs. This might be due to rate limiting or a network timeout.</p>
                <button wire:click="$refresh" class="flex items-center gap-2 bg-white hover:bg-gray-100 text-[#030712] px-6 py-3 rounded-full font-bold transition-all shadow-lg hover:shadow-white/20 hover:-translate-y-0.5 cursor-pointer">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                   Retry Request
                </button>
            @endif
        </div>
    @else

        <!-- Search Label -->
        @if($search)
        <div class="mb-8 flex items-center justify-between p-4 bg-slate-900/50 rounded-2xl border border-slate-800 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-brand-500/10 flex items-center justify-center text-brand-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div>
                    <p class="text-slate-400 text-sm">Search results for</p>
                    <p class="text-white font-bold text-lg">"{{ $search }}"</p>
                </div>
            </div>
            <button wire:click="$set('search', '')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-full text-sm font-medium transition-colors cursor-pointer">Clear</button>
        </div>
        @endif

        <!-- ── HERO SECTION ── -->
        @php $hero = $news[0]; @endphp
        <a href="{{ route('news.show', $hero['id'] ?? '#') }}" class="group block mb-12 cursor-pointer">
            <div class="relative w-full rounded-[2rem] overflow-hidden bg-slate-900 border border-slate-800 card-hover shadow-2xl aspect-[4/3] sm:aspect-[21/9]">
                @if($hero['image'])
                <img src="{{ $hero['image'] }}" alt="Hero Image" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000 ease-out" onerror="this.style.display='none'">
                @endif
                <div class="img-gradient z-10"></div>
                
                <div class="absolute inset-0 z-20 flex flex-col justify-end p-6 sm:p-10 lg:p-14">
                    <div class="flex items-center gap-3 mb-4 sm:mb-6">
                        <span class="bg-brand-500 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider backdrop-blur-md">Featured</span>
                        <span class="bg-black/50 backdrop-blur-md border border-white/10 text-white text-xs font-medium px-3 py-1.5 rounded-full flex items-center gap-2 hover:bg-black/70 transition-colors">
                            <span class="w-1.5 h-1.5 bg-brand-500 rounded-full"></span>
                            {{ $hero['source']['name'] }}
                        </span>
                        @if(!empty($hero['author']))
                        <span class="hidden sm:block text-slate-300 text-sm font-medium ml-2 border-l border-white/20 pl-3">
                            {{ \Illuminate\Support\Str::limit($hero['author'], 25) }}
                        </span>
                        @endif
                        <span class="hidden sm:block text-slate-400 text-sm font-medium ml-2">{{ \Carbon\Carbon::parse($hero['publishedAt'])->diffForHumans() }}</span>
                    </div>
                    
                    <h1 class="text-white text-2xl sm:text-4xl lg:text-5xl font-extrabold leading-[1.15] tracking-tight group-hover:text-brand-50 transition-colors max-w-4xl drop-shadow-md">
                        {{ $hero['title'] }}
                    </h1>
                    
                    @if($hero['description'])
                    <p class="text-slate-300 text-base sm:text-lg mt-4 max-w-3xl line-clamp-2 leading-relaxed hidden sm:block drop-shadow">
                        {{ $hero['description'] }}
                    </p>
                    @endif
                </div>
            </div>
        </a>

        <!-- ── NEWS GRID ── -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
            @foreach(array_slice($news, 1) as $idx => $article)
            
            {{-- Make the 4th item a wide feature card if on larger screens --}}
            @if($idx === 3 && count($news) > 5)
                <a href="{{ route('news.show', $article['id'] ?? '#') }}" class="group col-span-1 md:col-span-2 lg:col-span-2 xl:col-span-2 flex flex-col sm:flex-row bg-slate-900/60 rounded-3xl border border-slate-800 card-hover overflow-hidden relative backdrop-blur-sm">
                    <div class="w-full sm:w-2/5 aspect-[4/3] sm:aspect-auto relative overflow-hidden bg-slate-800/50">
                        @if($article['image'])
                        <img src="{{ $article['image'] }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="">
                        @endif
                        <div class="img-gradient sm:hidden z-10"></div>
                    </div>
                    <div class="w-full sm:w-3/5 p-6 sm:p-8 flex flex-col justify-center relative z-20">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="text-brand-500 text-xs font-bold uppercase tracking-widest">{{ $article['source']['name'] }}</span>
                            @if(!empty($article['author']))
                            <span class="hidden sm:inline-block text-slate-600 text-[10px]">/</span>
                            <span class="hidden sm:inline-block text-slate-400 text-xs font-semibold truncate max-w-[120px]" title="{{ $article['author'] }}">{{ $article['author'] }}</span>
                            @endif
                            <span class="w-1 h-1 bg-slate-700 rounded-full"></span>
                            <span class="text-slate-500 text-xs font-medium">{{ \Carbon\Carbon::parse($article['publishedAt'])->diffForHumans() }}</span>
                        </div>
                        <h3 class="text-white text-xl sm:text-2xl font-bold leading-tight group-hover:text-brand-400 transition-colors line-clamp-3">{{ $article['title'] }}</h3>
                        @if($article['description'])
                        <p class="text-slate-400 text-sm mt-4 line-clamp-2 leading-relaxed">{{ $article['description'] }}</p>
                        @endif
                    </div>
                </a>
            @else
                <!-- Standard Grid Card -->
                <a href="{{ route('news.show', $article['id'] ?? '#') }}" class="group flex flex-col bg-slate-900/40 rounded-3xl border border-slate-800/80 card-hover overflow-hidden backdrop-blur-sm relative h-full">
                    <div class="w-full aspect-[16/10] relative overflow-hidden bg-slate-800/30">
                        @if($article['image'])
                        <img src="{{ $article['image'] }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="">
                        @endif
                        <!-- Publisher Overlay -->
                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-black/60 backdrop-blur-md border border-white/10 text-white text-[10px] sm:text-xs font-bold px-2.5 py-1 sm:py-1.5 rounded-full uppercase tracking-wider">
                                {{ $article['source']['name'] }}
                            </span>
                        </div>
                    </div>
                    <div class="p-5 sm:p-6 flex flex-col flex-1 relative z-20 bg-gradient-to-b from-transparent to-slate-900/50">
                        <div class="flex items-center justify-between mb-3 text-xs">
                            <span class="text-slate-400 font-medium flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ \Carbon\Carbon::parse($article['publishedAt'])->diffForHumans(null, true, true) }} ago
                            </span>
                            @if(!empty($article['author']))
                            <span class="text-slate-500 font-semibold truncate max-w-[100px] text-right" title="{{ $article['author'] }}">
                                {{ $article['author'] }}
                            </span>
                            @endif
                        </div>
                        <h3 class="text-white text-base sm:text-lg font-bold leading-snug group-hover:text-brand-400 transition-colors line-clamp-3 mb-3">{{ $article['title'] }}</h3>
                        <div class="mt-auto pt-4 flex items-center justify-between border-t border-slate-800/50">
                            <span class="text-brand-500 text-sm font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                                Read article <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endif
            @endforeach
        </div>

        <!-- ── LOAD MORE / REFRESH ── -->
        <div class="mt-16 flex justify-center">
            <button wire:click="$refresh"
               class="group relative inline-flex items-center justify-center gap-3 px-8 py-4 bg-slate-900 border border-slate-700 hover:border-brand-500/50 rounded-full overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-brand-500/20 cursor-pointer">
                <div class="absolute inset-0 w-0 bg-gradient-to-r from-brand-600 to-rose-600 transition-all duration-500 ease-out group-hover:w-full z-0"></div>
                <!-- Rotate normally, but spin if loading -->
                <svg wire:loading.class="animate-spin" wire:loading.class.remove="group-hover:-rotate-180" class="w-5 h-5 text-slate-300 group-hover:text-white relative z-10 transition-colors group-hover:-rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span class="text-white font-bold text-sm tracking-wide relative z-10">Load Fresh Stories</span>
            </button>
        </div>
        
        @endif
    </main>
</div>
