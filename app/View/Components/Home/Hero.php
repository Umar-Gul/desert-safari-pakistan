@once
    <style>
        @keyframes hero-rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        @keyframes hero-zoom { from { transform: scale(1.07); } to { transform: scale(1); } }
        .hero-rise { opacity: 0; animation: hero-rise .75s cubic-bezier(.2, .7, .2, 1) forwards; }
        .hero-zoom { animation: hero-zoom 18s ease-out forwards; }
        @media (prefers-reduced-motion: reduce) {
            .hero-rise { opacity: 1; animation: none; }
            .hero-zoom { animation: none; }
        }
    </style>
@endonce

<section class="relative isolate flex min-h-[88svh] flex-col overflow-hidden bg-stone-950 text-white sm:min-h-[92vh]">

    {{-- Background --}}
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="" width="1920" height="1080" fetchpriority="high" decoding="async"
             class="hero-zoom absolute inset-0 -z-30 h-full w-full object-cover">
    @else
        <div class="absolute inset-0 -z-30 bg-gradient-to-br from-stone-950 via-amber-950 to-orange-900"></div>
        <div class="absolute -right-24 top-10 -z-20 h-[28rem] w-[28rem] rounded-full bg-amber-500/20 blur-3xl"></div>
        <div class="absolute -left-32 bottom-20 -z-20 h-[24rem] w-[24rem] rounded-full bg-orange-500/15 blur-3xl"></div>
        <svg class="absolute inset-x-0 bottom-0 -z-20 h-40 w-full text-stone-950/60 sm:h-56" viewBox="0 0 1440 220"
             preserveAspectRatio="none" fill="currentColor" aria-hidden="true">
            <path d="M0 150C160 90 280 100 420 140s260 70 420 20 300-90 600-10v120H0z"/>
        </svg>
    @endif
    <div class="absolute inset-0 -z-20 bg-gradient-to-b from-stone-950/70 via-stone-950/30 to-stone-950/85"></div>
    <div class="absolute inset-0 -z-20 bg-gradient-to-r from-stone-950/60 via-transparent to-transparent"></div>

    {{-- Headline --}}
    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col justify-center px-4 pb-10 pt-20 sm:px-6 sm:pt-28 lg:px-8">
        <div class="max-w-4xl">
            @if ($eyebrow)
                <p class="hero-rise flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.32em] text-amber-300 sm:text-sm">
                    <span class="h-px w-10 bg-amber-300/70" aria-hidden="true"></span>
                    {{ $eyebrow }}
                </p>
            @endif

            <h1 class="hero-rise mt-5 text-balance text-5xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl xl:text-[5.25rem]"
                style="animation-delay: .08s">
                {{ $title }}
            </h1>

            @if ($subtitle)
                <p class="hero-rise mt-6 max-w-2xl text-lg leading-8 text-stone-200 sm:text-xl" style="animation-delay: .16s">
                    {{ $subtitle }}
                </p>
            @endif

            <div class="hero-rise mt-8 flex flex-col gap-3 sm:flex-row sm:items-center" style="animation-delay: .24s">
                <a href="{{ $toursUrl }}"
                   class="inline-flex min-h-12 items-center justify-center rounded-full bg-amber-400 px-8 text-base font-bold text-stone-950 shadow-lg shadow-amber-950/30 transition hover:bg-amber-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-stone-950">
                    {{ $ctaText ?: 'Explore all tours' }}
                    <svg class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </a>

                <a href="{{ whatsapp_url('Hello, I would like to book a desert safari.') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/30 bg-white/10 px-7 text-base font-semibold text-white backdrop-blur-md transition hover:border-white/60 hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    <svg class="mr-2 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2z"/>
                    </svg>
                    Chat on WhatsApp
                </a>
            </div>

            @if ($search && Route::has('search'))
                <form action="{{ route('search') }}" method="GET" role="search"
                      class="hero-rise mt-6 flex max-w-xl items-center gap-2 rounded-full border border-white/20 bg-white/10 p-1.5 backdrop-blur-md"
                      style="animation-delay: .3s">
                    <label for="hero-search" class="sr-only">Search tours</label>
                    <input id="hero-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search safaris, camel rides, camping..."
                           class="min-h-11 w-full border-0 bg-transparent px-4 text-sm text-white placeholder:text-stone-300 focus:ring-0">
                    <button type="submit" class="min-h-11 shrink-0 rounded-full bg-white px-5 text-sm font-semibold text-stone-900 hover:bg-amber-100">Search</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Category cards --}}
    @if ($cards->isNotEmpty())
        <div class="mx-auto w-full max-w-7xl px-4 pb-10 sm:px-6 sm:pb-14 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 {{ $cards->count() > 2 ? 'lg:grid-cols-3' : '' }}">
                @foreach ($cards as $card)
                    <a href="{{ $card['url'] }}"
                       class="hero-rise group relative isolate flex min-h-[8.5rem] items-end overflow-hidden rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:border-amber-300/60 hover:bg-white/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 sm:p-6"
                       style="animation-delay: {{ number_format(0.3 + $loop->index * 0.08, 2) }}s">

                        @if ($card['image'])
                            <img src="{{ $card['image'] }}" alt="" loading="lazy" decoding="async"
                                 class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40 transition duration-500 group-hover:scale-105 group-hover:opacity-55">
                        @endif

                        <div class="flex w-full items-end justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold sm:text-2xl">{{ $card['name'] }}</h2>
                                <p class="mt-1 text-sm text-stone-200">
                                    {{ $card['count'] }} {{ \Illuminate\Support\Str::plural('tour', $card['count']) }}
                                    @if ($card['from'])
                                        from <span class="font-semibold text-amber-300">{{ $currency }} {{ $card['from'] }}</span>
                                    @endif
                                </p>
                            </div>

                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/15 text-white transition group-hover:bg-amber-400 group-hover:text-stone-950" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M9 7h8v8"/>
                                </svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>