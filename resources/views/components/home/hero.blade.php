@props([
    'imageUrl' => null,
    'eyebrow' => 'Desert Safari Pakistan',
    'title' => "Explore Pakistan's\nGolden Deserts",
    'subtitle' => 'Guided safaris in Cholistan, Thar and Thal. Dune drives, camel rides, folk music nights and starlit camps.',
    'toursUrl' => null,
    'ctaText' => 'Browse Tours',
    'search' => false,
    'cards' => [],
    'currency' => 'PKR',
])

@php
    $toursUrl = $toursUrl ?: route('tours.index');
    $heroImage = $imageUrl ?: asset('images/desert-safari-hero.png');

    $categoryUrl = fn (string $slug) => \Illuminate\Support\Facades\Route::has('categories.show')
        ? route('categories.show', $slug)
        : $toursUrl;

    $cards = collect($cards)->values();

    // Fallback cards (same four categories as the navbar). Each photo has a main link
    // ('image' / 'hero') and a backup link ('fallback' / 'hero_fallback') that is tried
    // automatically if the main one fails. Replace the links with your own photos any time,
    // for example asset('images/hero/dunes.jpg').
    if ($cards->isEmpty()) {
        $cards = collect([
            [
                'name'  => 'Morning Safari',
                'note'  => 'Sunrise dune drive',
                'image' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?auto=format&fit=crop&w=700&q=80',
                'hero'  => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?auto=format&fit=crop&w=1920&q=80',
                'fallback'      => 'https://loremflickr.com/700/500/desert,sand?lock=11',
                'hero_fallback' => 'https://loremflickr.com/1920/1080/desert,sand?lock=11',
                'url'   => $categoryUrl('morning-safari'),
            ],
            [
                'name'  => 'Evening Safari',
                'note'  => 'Sunset, camp and dinner',
                'image' => 'https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?auto=format&fit=crop&w=700&q=80',
                'hero'  => 'https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?auto=format&fit=crop&w=1920&q=80',
                'fallback'      => 'https://loremflickr.com/700/500/dunes,sunset?lock=33',
                'hero_fallback' => 'https://loremflickr.com/1920/1080/dunes,sunset?lock=33',
                'url'   => $categoryUrl('evening-safari'),
            ],
            [
                'name'  => 'Premium Safari',
                'note'  => 'Private 4x4 and VIP camp',
                'image' => 'https://images.unsplash.com/photo-1473580044384-7ba9967e16a0?auto=format&fit=crop&w=700&q=80',
                'hero'  => 'https://images.unsplash.com/photo-1473580044384-7ba9967e16a0?auto=format&fit=crop&w=1920&q=80',
                'fallback'      => 'https://loremflickr.com/700/500/camel,desert?lock=22',
                'hero_fallback' => 'https://loremflickr.com/1920/1080/camel,desert?lock=22',
                'url'   => $categoryUrl('premium-safari'),
            ],
            [
                'name'  => 'Quad Biking & Dirt Bikes',
                'note'  => 'Dunes at full throttle',
                'image' => 'https://images.unsplash.com/photo-1489493887464-892be6d1daae?auto=format&fit=crop&w=700&q=80',
                'hero'  => 'https://images.unsplash.com/photo-1489493887464-892be6d1daae?auto=format&fit=crop&w=1920&q=80',
                'fallback'      => 'https://loremflickr.com/700/500/desert,jeep?lock=44',
                'hero_fallback' => 'https://loremflickr.com/1920/1080/desert,jeep?lock=44',
                'url'   => $categoryUrl('quad-biking-dirt-bikes'),
            ],
        ]);
    }

    $cards = $cards->take(4)->values();

    // One background per card, in the same order as the cards.
    $slides = $cards->map(fn ($c) => [
        'src'      => $c['hero'] ?? $c['image'] ?? $heroImage,
        'fallback' => $c['hero_fallback'] ?? $c['fallback'] ?? '',
    ])->values();

    // Slow camera drift for each slide (x, y) so every background moves differently.
    $drift = [['-2%', '-1.5%'], ['2%', '-1%'], ['-1.5%', '1.5%'], ['2%', '1.5%']];

    // First title line is white, the rest is highlighted in gold.
    $titleLines = preg_split('/\R/', trim($title));
    $titleFirst = array_shift($titleLines);
@endphp

@once
<style>
    @keyframes safariRise {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes safariGlow {
        from { opacity: .55; transform: translate3d(0, 0, 0) scale(1); }
        to   { opacity: .95; transform: translate3d(-6%, 5%, 0) scale(1.15); }
    }

    @keyframes safariDust {
        0%   { opacity: 0; transform: translate3d(0, 0, 0); }
        12%  { opacity: .85; }
        85%  { opacity: .45; }
        100% { opacity: 0; transform: translate3d(70px, -720px, 0); }
    }

    .safari-hero {
        position: relative;
        isolation: isolate;
        display: flex;
        min-height: clamp(560px, 80vh, 760px);
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        background: linear-gradient(135deg, #7c2d12 0%, #3b1608 100%);
        color: #fff;
        font-family: inherit;
    }

    /* ---------- Moving background (looks like video) ---------- */
    .safari-hero__background,
    .safari-hero__slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 48%;
    }

    .safari-hero__background {
        z-index: -4;
    }

    /* Inactive: invisible, and the zoom resets only after the fade-out has finished */
    .safari-hero__slide {
        z-index: -3;
        opacity: 0;
        transform: scale(1) translate(0, 0);
        transition: opacity 1.2s ease, transform 0s linear 1.2s;
        will-change: opacity, transform;
    }

    /* Active: fades in while slowly zooming and drifting */
    .safari-hero__slide.is-active {
        opacity: 1;
        transform: scale(1.14) translate(var(--dx, 0), var(--dy, 0));
        transition: opacity 1.2s ease, transform 9s linear;
    }

    .safari-hero__overlay {
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            linear-gradient(90deg, rgba(30, 10, 3, .82) 0%, rgba(30, 10, 3, .45) 55%, rgba(30, 10, 3, .2) 100%),
            linear-gradient(0deg, rgba(30, 10, 3, .85) 0%, transparent 45%),
            linear-gradient(180deg, rgba(30, 10, 3, .35) 0%, transparent 25%);
    }

    /* Drifting sunlight */
    .safari-hero__glow {
        position: absolute;
        z-index: -1;
        top: -22%;
        right: -12%;
        width: 62%;
        aspect-ratio: 1;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(251, 191, 36, .32) 0%, transparent 62%);
        mix-blend-mode: screen;
        pointer-events: none;
        animation: safariGlow 12s ease-in-out infinite alternate;
    }

    /* Floating sand / dust */
    .safari-hero__dust {
        position: absolute;
        inset: 0;
        z-index: -1;
        overflow: hidden;
        pointer-events: none;
    }

    .safari-hero__dust span {
        position: absolute;
        bottom: -12px;
        left: var(--x);
        width: var(--s);
        height: var(--s);
        border-radius: 50%;
        background: rgba(255, 228, 160, .7);
        opacity: 0;
        animation: safariDust var(--t) linear var(--d) infinite;
    }

    /* ---------- Layout ---------- */
    .safari-hero__content {
        width: 100%;
        max-width: 1280px;
        margin-inline: auto;
        padding: 0 24px;
    }

    .safari-hero__copy {
        max-width: 680px;
        padding: 72px 0 36px;
    }

    .safari-hero__rise {
        opacity: 0;
        animation: safariRise .85s cubic-bezier(.2, .7, .2, 1) var(--delay, 0s) forwards;
    }

    .safari-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 16px;
        color: #ffe9a8;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .safari-hero__eyebrow::before {
        width: 32px;
        height: 2px;
        background: #fbbf24;
        content: "";
    }

    .safari-hero__title {
        margin: 0;
        color: #fff;
        font-size: clamp(38px, 5.2vw, 72px);
        font-weight: 900;
        line-height: 1.04;
        letter-spacing: -.03em;
        text-shadow: 0 4px 18px rgba(0, 0, 0, .45);
        text-wrap: balance;
    }

    .safari-hero__title-accent {
        display: block;
        color: #fbbf24;
    }

    .safari-hero__subtitle {
        max-width: 540px;
        margin: 18px 0 0;
        color: #fff;
        font-size: 18px;
        line-height: 1.65;
        text-shadow: 0 2px 8px rgba(0, 0, 0, .6);
    }

    .safari-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
    }

    .safari-hero__button {
        display: inline-flex;
        min-height: 52px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 12px 28px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
        transition: background .25s ease, transform .25s ease;
    }

    .safari-hero__button--primary {
        background: #fbbf24;
        color: #2b1305;
        box-shadow: 0 6px 22px rgba(0, 0, 0, .3);
    }

    .safari-hero__button--primary:hover {
        background: #f59e0b;
        transform: translateY(-2px);
    }

    .safari-hero__button--secondary {
        border-color: rgba(255, 255, 255, .7);
        background: rgba(255, 255, 255, .12);
        color: #fff;
        backdrop-filter: blur(8px);
    }

    .safari-hero__button--secondary:hover {
        background: rgba(255, 255, 255, .26);
    }

    .safari-hero a:focus-visible,
    .safari-hero button:focus-visible,
    .safari-hero input:focus-visible {
        outline: 3px solid #fde68a;
        outline-offset: 3px;
    }

    /* ---------- Search (optional) ---------- */
    .safari-hero__search {
        display: flex;
        max-width: 540px;
        gap: 8px;
        margin-top: 18px;
        padding: 5px;
        border: 1px solid rgba(255, 255, 255, .45);
        border-radius: 999px;
        background: rgba(255, 255, 255, .14);
        backdrop-filter: blur(10px);
    }

    .safari-hero__search input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: none;
        background: transparent;
        padding: 10px 16px;
        color: #fff;
    }

    .safari-hero__search input::placeholder {
        color: rgba(255, 255, 255, .85);
    }

    .safari-hero__search button {
        flex-shrink: 0;
        border: 0;
        border-radius: 999px;
        background: #fff;
        padding: 10px 20px;
        color: #7c2d12;
        font-weight: 800;
        cursor: pointer;
    }

    /* ---------- Category cards ---------- */
    .safari-hero__bottom {
        padding-bottom: 28px;
    }

    .safari-hero__pause {
        display: flex;
        width: 38px;
        height: 38px;
        align-items: center;
        justify-content: center;
        margin: 0 0 10px auto;
        border: 1px solid rgba(255, 255, 255, .5);
        border-radius: 999px;
        background: rgba(0, 0, 0, .35);
        color: #fff;
        cursor: pointer;
        backdrop-filter: blur(6px);
    }

    .safari-hero__pause:hover {
        background: rgba(0, 0, 0, .55);
    }

    .safari-hero__cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .safari-hero__card {
        position: relative;
        display: flex;
        min-height: 150px;
        align-items: flex-end;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, .5);
        border-radius: 14px;
        /* Sand gradient shows if no photo loads, so cards never look empty */
        background: linear-gradient(135deg, #d97706 0%, #92400e 55%, #451a03 100%);
        color: #fff;
        text-decoration: none;
        box-shadow: 0 5px 16px rgba(0, 0, 0, .3);
        transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
    }

    .safari-hero__card:hover,
    .safari-hero__card.is-active {
        border-color: #fbbf24;
    }

    .safari-hero__card.is-active {
        transform: translateY(-5px);
        box-shadow: 0 14px 28px rgba(0, 0, 0, .45);
    }

    .safari-hero__card img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s ease;
    }

    .safari-hero__card:hover img,
    .safari-hero__card.is-active img {
        transform: scale(1.08);
    }

    .safari-hero__card::after {
        position: absolute;
        inset: 0;
        background: linear-gradient(0deg, rgba(25, 10, 3, .92), rgba(25, 10, 3, .05) 78%);
        content: "";
    }

    .safari-hero__card-text {
        position: relative;
        z-index: 1;
        width: 100%;
        padding: 14px;
    }

    .safari-hero__card-title {
        display: block;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.25;
    }

    .safari-hero__card-meta {
        display: block;
        margin-top: 4px;
        color: #ffe4a2;
        font-size: 12px;
        font-weight: 700;
    }

    .safari-hero__progress {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 2;
        height: 4px;
        background: rgba(255, 255, 255, .25);
    }

    .safari-hero__progress > span {
        display: block;
        height: 100%;
        background: #fbbf24;
        transition: width .1s linear;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 900px) {
        .safari-hero {
            min-height: 0;
        }

        .safari-hero__content {
            padding: 0 20px;
        }

        .safari-hero__copy {
            padding: 52px 0 28px;
        }

        .safari-hero__cards {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .safari-hero__overlay {
            background:
                linear-gradient(90deg, rgba(30, 10, 3, .78), rgba(30, 10, 3, .45)),
                linear-gradient(0deg, rgba(30, 10, 3, .92), rgba(30, 10, 3, .15) 100%);
        }

        .safari-hero__content {
            padding: 0 16px;
        }

        .safari-hero__copy {
            padding: 40px 0 22px;
        }

        .safari-hero__eyebrow {
            font-size: 11px;
        }

        .safari-hero__title {
            font-size: clamp(34px, 10.5vw, 50px);
        }

        .safari-hero__subtitle {
            font-size: 15px;
        }

        .safari-hero__actions {
            margin-top: 22px;
        }

        .safari-hero__button {
            flex: 1 1 100%;
            min-height: 50px;
        }

        .safari-hero__cards {
            gap: 10px;
        }

        .safari-hero__card {
            min-height: 120px;
            border-radius: 10px;
        }

        .safari-hero__card-text {
            padding: 10px;
        }

        .safari-hero__card-title {
            font-size: 13px;
        }

        .safari-hero__card-meta {
            font-size: 11px;
        }

        .safari-hero__dust span:nth-child(n+8) {
            display: none;
        }
    }

    /* Respect "reduce motion": a still hero, no drifting or dust */
    @media (prefers-reduced-motion: reduce) {
        .safari-hero__slide.is-active {
            transform: none;
            transition: opacity .6s ease;
        }

        .safari-hero__rise {
            opacity: 1;
            animation: none;
        }

        .safari-hero__glow,
        .safari-hero__dust {
            display: none;
        }

        .safari-hero *,
        .safari-hero *::before,
        .safari-hero *::after {
            transition-duration: .01s !important;
        }
    }
</style>
@endonce

<section
    class="safari-hero"
    x-data="{
        active: 0,
        total: {{ $cards->count() }},
        elapsed: 0,
        duration: 7000,
        paused: false,
        hold: false,
        init() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.paused = true;
            if (this.total > 1) setInterval(() => this.tick(), 100);
        },
        tick() {
            if (this.paused || this.hold || document.hidden) return;
            this.elapsed += 100;
            if (this.elapsed >= this.duration) this.go((this.active + 1) % this.total);
        },
        go(i) {
            this.active = i;
            this.elapsed = 0;
        }
    }"
>
    {{-- Base background (hidden automatically if the file does not exist) --}}
    <img
        class="safari-hero__background"
        src="{{ $heroImage }}"
        alt=""
        width="1920"
        height="1080"
        decoding="async"
        onerror="this.style.display='none'"
    >

    {{-- Slideshow backgrounds: one per card, each with its own slow camera drift --}}
    @foreach ($slides as $i => $slide)
        <img
            class="safari-hero__slide {{ $i === 0 ? 'is-active' : '' }}"
            :class="{ 'is-active': active === {{ $i }} }"
            style="--dx: {{ $drift[$i % 4][0] }}; --dy: {{ $drift[$i % 4][1] }};"
            src="{{ $slide['src'] }}"
            data-fb="{{ $slide['fallback'] }}"
            alt=""
            width="1920"
            height="1080"
            decoding="async"
            @if ($i === 0) fetchpriority="high" @endif
            onerror="if(!this.dataset.f && this.dataset.fb){this.dataset.f='1';this.src=this.dataset.fb;}else{this.style.display='none';}"
        >
    @endforeach

    <div class="safari-hero__overlay" aria-hidden="true"></div>
    <div class="safari-hero__glow" aria-hidden="true"></div>

    <div class="safari-hero__dust" aria-hidden="true">
        @for ($p = 0; $p < 14; $p++)
            <span style="--x: {{ ($p * 37 + 11) % 100 }}%; --s: {{ 2 + ($p % 3) }}px; --t: {{ 9 + ($p % 5) * 2 }}s; --d: -{{ ($p * 1.3) }}s;"></span>
        @endfor
    </div>

    <div class="safari-hero__content">
        <div class="safari-hero__copy">
            @if ($eyebrow)
                <p class="safari-hero__eyebrow safari-hero__rise" style="--delay: .1s">{{ $eyebrow }}</p>
            @endif

            <h1 class="safari-hero__title safari-hero__rise" style="--delay: .25s">
                {{ $titleFirst }}
                @if (count($titleLines))
                    <span class="safari-hero__title-accent">{!! nl2br(e(implode("\n", $titleLines))) !!}</span>
                @endif
            </h1>

            @if ($subtitle)
                <p class="safari-hero__subtitle safari-hero__rise" style="--delay: .4s">{{ $subtitle }}</p>
            @endif

            <div class="safari-hero__actions safari-hero__rise" style="--delay: .55s">
                <a href="{{ $toursUrl }}" class="safari-hero__button safari-hero__button--primary">
                    {{ $ctaText ?: 'Browse Tours' }}
                    <span aria-hidden="true">&rarr;</span>
                </a>

                @if (function_exists('whatsapp_url'))
                    <a
                        href="{{ whatsapp_url('Hello, I would like to book a desert safari.') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="safari-hero__button safari-hero__button--secondary"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        Chat on WhatsApp
                    </a>
                @endif
            </div>

            @if ($search && \Illuminate\Support\Facades\Route::has('search'))
                <form action="{{ route('search') }}" method="GET" role="search" class="safari-hero__search safari-hero__rise" style="--delay: .65s">
                    <label for="hero-search" class="sr-only">Search tours</label>
                    <input
                        id="hero-search"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        autocomplete="off"
                        placeholder="Search safaris, quad biking, camping..."
                    >
                    <button type="submit">Search</button>
                </form>
            @endif
        </div>
    </div>

    @if ($cards->isNotEmpty())
        <div class="safari-hero__content safari-hero__bottom">
            @if ($cards->count() > 1)
                <button
                    type="button"
                    class="safari-hero__pause"
                    x-on:click="paused = !paused"
                    :aria-pressed="paused.toString()"
                    :aria-label="paused ? 'Play background slideshow' : 'Pause background slideshow'"
                >
                    <svg x-show="!paused" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>
                    <svg x-show="paused" x-cloak width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4l13 8-13 8z"/></svg>
                </button>
            @endif

            {{-- The slideshow pauses only while the pointer or keyboard focus is on the cards --}}
            <div
                class="safari-hero__cards"
                x-on:mouseenter="hold = true"
                x-on:mouseleave="hold = false"
                x-on:focusin="hold = true"
                x-on:focusout="hold = false"
            >
                @foreach ($cards as $i => $card)
                    <a
                        href="{{ $card['url'] ?? $toursUrl }}"
                        class="safari-hero__card"
                        :class="{ 'is-active': active === {{ $i }} }"
                        x-on:mouseenter="go({{ $i }})"
                        x-on:focus="go({{ $i }})"
                    >
                        @if (!empty($card['image']))
                            <img
                                src="{{ $card['image'] }}"
                                data-fb="{{ $card['fallback'] ?? '' }}"
                                alt=""
                                loading="eager"
                                decoding="async"
                                width="600"
                                height="350"
                                onerror="if(!this.dataset.f && this.dataset.fb){this.dataset.f='1';this.src=this.dataset.fb;}else{this.style.display='none';}"
                            >
                        @endif

                        <span class="safari-hero__card-text">
                            <span class="safari-hero__card-title">{{ $card['name'] ?? 'Explore Tours' }}</span>

                            <span class="safari-hero__card-meta">
                                @if (isset($card['count']))
                                    {{ $card['count'] }} {{ \Illuminate\Support\Str::plural('tour', $card['count']) }}
                                @elseif (!empty($card['note']))
                                    {{ $card['note'] }}
                                @else
                                    Discover the experience
                                @endif

                                @if (!empty($card['from']))
                                    &middot; From {{ $currency }} {{ number_format((float) $card['from']) }}
                                @endif
                            </span>
                        </span>

                        <span class="safari-hero__progress" x-show="active === {{ $i }}" aria-hidden="true">
                            <span :style="`width: ${Math.min(100, (elapsed / duration) * 100)}%`"></span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>