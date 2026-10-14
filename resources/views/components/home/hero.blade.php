@props([
    'imageUrl' => null,
    'eyebrow' => 'Desert Safari Pakistan',
    'title' => "Explore Pakistan's Golden Deserts",
    'subtitle' => null,
    'regions' => ['Cholistan', 'Thar', 'Thal'],
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

    // Real categories with the cover image uploaded from the dashboard.
    if ($cards->isEmpty() && class_exists(\App\Models\Category::class)) {
        $stock = [
            ['https://images.unsplash.com/photo-1509316785289-025f5b846b35?auto=format&fit=crop&w=%d&q=85', 'desert,sand', 11],
            ['https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?auto=format&fit=crop&w=%d&q=85', 'dunes,sunset', 33],
            ['https://images.unsplash.com/photo-1473580044384-7ba9967e16a0?auto=format&fit=crop&w=%d&q=85', 'camel,desert', 22],
            ['https://images.unsplash.com/photo-1489493887464-892be6d1daae?auto=format&fit=crop&w=%d&q=85', 'desert,jeep', 44],
        ];

        // $size = 'hero' -> ORIGINAL full-size upload (sharp, never a small thumbnail)
        // $size = 'card' -> small WebP conversion when it exists (fast), else the original
        // Always returns a path-only URL (/storage/...) so it works on any host or port.
        $coverOf = function ($c, string $size = 'card') {
            $url = '';
            try {
                if (method_exists($c, 'getFirstMediaUrl')) {
                    if ($size === 'card') {
                        try { $url = (string) $c->getFirstMediaUrl('cover', 'webp'); } catch (\Throwable $e) { $url = ''; }
                    }
                    if (! $url) { $url = (string) $c->getFirstMediaUrl('cover'); }
                }
                if (! $url && method_exists($c, 'coverUrl')) {
                    $url = (string) $c->coverUrl();
                }
            } catch (\Throwable $e) {
                $url = '';
            }

            return $url && preg_match('#^https?://#i', $url)
                ? (parse_url($url, PHP_URL_PATH) ?: $url)
                : $url;
        };

        try {
            $model = \App\Models\Category::class;
            $table = (new $model)->getTable();
            $schema = \Illuminate\Support\Facades\Schema::class;

            $query = $model::query();
            if ($schema::hasColumn($table, 'is_active')) $query->where('is_active', true);
            if ($schema::hasColumn($table, 'sort_order')) $query->orderBy('sort_order');
            $query->orderBy('name');
            if (method_exists($model, 'media')) $query->with('media');
            if (method_exists($model, 'tours')) $query->withCount('tours');

            $cards = $query->take(4)->get()->values()->map(function ($c, $i) use ($categoryUrl, $stock, $coverOf) {
                $s = $stock[$i % 4];
                $card = $coverOf($c, 'card');
                $hero = $coverOf($c, 'hero');

                return [
                    'name'          => $c->name,
                    'count'         => $c->tours_count ?? null,
                    'image'         => $card ?: sprintf($s[0], 700),
                    'hero'          => $hero ?: sprintf($s[0], 2400),
                    'fallback'      => "https://loremflickr.com/700/500/{$s[1]}?lock={$s[2]}",
                    'hero_fallback' => "https://loremflickr.com/1920/1080/{$s[1]}?lock={$s[2]}",
                    'url'           => $categoryUrl($c->slug),
                ];
            });
        } catch (\Throwable $e) {
            report($e);
            $cards = collect();
        }
    }

    // Fallback cards if there are no categories yet.
    if ($cards->isEmpty()) {
        $cards = collect([
            ['name' => 'Morning Safari', 'note' => 'Sunrise dune drive', 'img' => '1509316785289-025f5b846b35', 'q' => 'desert,sand', 'l' => 11, 'slug' => 'morning-safari'],
            ['name' => 'Evening Safari', 'note' => 'Sunset, camp and dinner', 'img' => '1451337516015-6b6e9a44a8a3', 'q' => 'dunes,sunset', 'l' => 33, 'slug' => 'evening-safari'],
            ['name' => 'Premium Safari', 'note' => 'Private 4x4 and VIP camp', 'img' => '1473580044384-7ba9967e16a0', 'q' => 'camel,desert', 'l' => 22, 'slug' => 'premium-safari'],
            ['name' => 'Quad Biking & Dirt Bikes', 'note' => 'Dunes at full throttle', 'img' => '1489493887464-892be6d1daae', 'q' => 'desert,jeep', 'l' => 44, 'slug' => 'quad-biking-dirt-bikes'],
        ])->map(fn ($c) => [
            'name'          => $c['name'],
            'note'          => $c['note'],
            'image'         => "https://images.unsplash.com/photo-{$c['img']}?auto=format&fit=crop&w=700&q=85",
            'hero'          => "https://images.unsplash.com/photo-{$c['img']}?auto=format&fit=crop&w=2400&q=85",
            'fallback'      => "https://loremflickr.com/700/500/{$c['q']}?lock={$c['l']}",
            'hero_fallback' => "https://loremflickr.com/1920/1080/{$c['q']}?lock={$c['l']}",
            'url'           => $categoryUrl($c['slug']),
        ]);
    }

    $cards = $cards->take(4)->values();

    $slides = $cards->map(fn ($c) => [
        'src'      => $c['hero'] ?? $c['image'] ?? $heroImage,
        'fallback' => $c['hero_fallback'] ?? $c['fallback'] ?? '',
    ])->values();

    // Subtle camera drift for each slide (x, y). Small values keep the photo sharp.
    $drift = [['-1%', '-.8%'], ['1%', '-.6%'], ['-.8%', '.8%'], ['1%', '.8%']];

    // One short professional line; the last two words are highlighted in gold.
    $headline = trim(preg_replace('/\s+/', ' ', (string) $title));
    $words = $headline === '' ? [] : explode(' ', $headline);
    $headFirst = count($words) > 3 ? implode(' ', array_slice($words, 0, -2)) : $headline;
    $headAccent = count($words) > 3 ? implode(' ', array_slice($words, -2)) : '';
    $totalTours = $cards->sum(fn ($c) => (int) ($c['count'] ?? 0));
@endphp

@once
<style>
    @keyframes safariRise {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes safariDust {
        0%   { opacity: 0; transform: translate3d(0, 0, 0); }
        12%  { opacity: .7; }
        85%  { opacity: .3; }
        100% { opacity: 0; transform: translate3d(50px, -620px, 0); }
    }
    @keyframes safariPing {
        0%   { box-shadow: 0 0 0 0 rgba(251, 191, 36, .6); }
        100% { box-shadow: 0 0 0 9px rgba(251, 191, 36, 0); }
    }

    .safari-hero {
        --hero-top: 80px;
        position: relative;
        isolation: isolate;
        display: flex;
        height: max(600px, calc(100vh - var(--hero-top)));
        height: max(600px, calc(100svh - var(--hero-top)));
        flex-direction: column;
        overflow: hidden;
        background: #1c0a02;
        color: #fff;
        font-family: inherit;
    }

    /* ---------- Sharp full-HD background ---------- */
    .safari-hero__background,
    .safari-hero__slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 50%;
    }
    .safari-hero__background { z-index: -4; }
    .safari-hero__slide {
        z-index: -3;
        opacity: 0;
        transform: scale(1) translate(0, 0);
        transition: opacity 1.2s ease, transform 0s linear 1.2s;
        will-change: opacity, transform;
        backface-visibility: hidden;
    }
    .safari-hero__slide.is-active {
        opacity: 1;
        transform: scale(1.05) translate(var(--dx, 0), var(--dy, 0));
        transition: opacity 1.2s ease, transform 9s linear;
    }
    .safari-hero__overlay {
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            linear-gradient(90deg, rgba(18, 7, 2, .72) 0%, rgba(18, 7, 2, .3) 48%, rgba(18, 7, 2, 0) 78%),
            linear-gradient(0deg, rgba(18, 7, 2, .78) 0%, rgba(18, 7, 2, 0) 42%),
            linear-gradient(180deg, rgba(18, 7, 2, .3) 0%, rgba(18, 7, 2, 0) 20%);
    }
    .safari-hero__dust { position: absolute; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
    .safari-hero__dust span {
        position: absolute; bottom: -12px; left: var(--x);
        width: var(--s); height: var(--s); border-radius: 50%;
        background: rgba(255, 228, 160, .6); opacity: 0;
        animation: safariDust var(--t) linear var(--d) infinite;
    }

    /* ---------- Layout ---------- */
    .safari-hero__content { width: 100%; max-width: 1280px; margin-inline: auto; padding: 0 24px; }
    .safari-hero__main { display: flex; flex: 1; align-items: center; }
    .safari-hero__copy { max-width: 660px; padding: 28px 0; }
    .safari-hero__rise { opacity: 0; animation: safariRise .75s cubic-bezier(.2, .7, .2, 1) var(--delay, 0s) forwards; }

    /* ---------- Text ---------- */
    .safari-hero__badge {
        display: inline-flex; align-items: center; gap: 9px;
        margin: 0 0 16px; padding: 7px 14px 7px 12px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 999px;
        background: rgba(255, 255, 255, .1);
        backdrop-filter: blur(10px);
        color: #fff; font-size: 12px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase;
    }
    .safari-hero__badge i {
        width: 8px; height: 8px; border-radius: 50%;
        background: #fbbf24; animation: safariPing 1.8s ease-out infinite;
    }

    .safari-hero__title {
        margin: 0;
        font-size: clamp(28px, 3.6vw, 52px);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.02em;
        text-shadow: 0 3px 18px rgba(0, 0, 0, .5);
        text-wrap: balance;
    }
    .safari-hero__title em {
        font-style: normal;
        background: linear-gradient(90deg, #fde68a, #f59e0b);
        -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent; color: transparent;
    }

    .safari-hero__regions {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        margin: 14px 0 0; padding: 0; list-style: none;
        font-size: 14px; font-weight: 600; color: rgba(255, 255, 255, .92);
        text-shadow: 0 1px 8px rgba(0, 0, 0, .6);
    }
    .safari-hero__regions li + li::before {
        content: ""; display: inline-block; width: 4px; height: 4px; margin-right: 8px;
        border-radius: 50%; background: #fbbf24; vertical-align: middle;
    }

    /* ---------- Buttons ---------- */
    .safari-hero__actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
    .safari-hero__button {
        position: relative; display: inline-flex; min-height: 54px;
        align-items: center; justify-content: center; gap: 10px;
        padding: 0 26px; border: 1px solid transparent; border-radius: 14px;
        font-size: 15px; font-weight: 700; line-height: 1; text-decoration: none; cursor: pointer;
        transition: transform .2s ease, box-shadow .25s ease, background-color .2s ease, border-color .2s ease;
    }
    .safari-hero__button:hover { transform: translateY(-2px); }
    .safari-hero__button:active { transform: scale(.97); }

    .safari-hero__button--primary {
        background: linear-gradient(180deg, #fcd34d 0%, #f59e0b 100%);
        color: #2b1305;
        box-shadow: 0 10px 26px rgba(245, 158, 11, .45), inset 0 1px 0 rgba(255, 255, 255, .55);
    }
    .safari-hero__button--primary:hover { box-shadow: 0 14px 32px rgba(245, 158, 11, .6), inset 0 1px 0 rgba(255, 255, 255, .55); }
    .safari-hero__arrow {
        display: grid; width: 26px; height: 26px; place-items: center;
        border-radius: 50%; background: rgba(43, 19, 5, .14); transition: transform .2s ease, background-color .2s;
    }
    .safari-hero__button--primary:hover .safari-hero__arrow { transform: translateX(3px); background: rgba(43, 19, 5, .22); }

    .safari-hero__button--whatsapp {
        border-color: rgba(255, 255, 255, .38);
        background: rgba(255, 255, 255, .12);
        backdrop-filter: blur(12px);
        color: #fff;
    }
    .safari-hero__button--whatsapp svg { color: #25d366; }
    .safari-hero__button--whatsapp:hover { background: rgba(255, 255, 255, .22); border-color: rgba(255, 255, 255, .6); }

    .safari-hero a:focus-visible,
    .safari-hero button:focus-visible,
    .safari-hero input:focus-visible { outline: 3px solid #fff; outline-offset: 3px; }

    /* ---------- Search (optional) ---------- */
    .safari-hero__search {
        display: flex; max-width: 520px; gap: 8px; margin-top: 16px; padding: 5px;
        border: 1px solid rgba(255, 255, 255, .4); border-radius: 14px;
        background: rgba(0, 0, 0, .35); backdrop-filter: blur(8px);
    }
    .safari-hero__search input { width: 100%; min-width: 0; border: 0; outline: none; background: transparent; padding: 10px 14px; color: #fff; }
    .safari-hero__search input::placeholder { color: rgba(255, 255, 255, .8); }
    .safari-hero__search button { flex-shrink: 0; border: 0; border-radius: 10px; background: #fbbf24; padding: 10px 18px; color: #2b1305; font-weight: 700; cursor: pointer; }

    /* ---------- Category cards ---------- */
    .safari-hero__bottom { padding-bottom: 22px; }
    .safari-hero__bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .safari-hero__bar-label { font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: rgba(255, 255, 255, .85); text-shadow: 0 1px 6px rgba(0, 0, 0, .6); }
    .safari-hero__bar-label b { color: #fbbf24; }
    .safari-hero__pause {
        display: flex; width: 34px; height: 34px; align-items: center; justify-content: center;
        border: 1px solid rgba(255, 255, 255, .45); border-radius: 50%;
        background: rgba(0, 0, 0, .35); color: #fff; cursor: pointer; backdrop-filter: blur(6px);
    }
    .safari-hero__pause:hover { background: rgba(0, 0, 0, .6); }

    .safari-hero__cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }

    .safari-hero__card {
        position: relative; display: flex; min-height: 128px; align-items: flex-end; overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .35); border-radius: 16px;
        background: linear-gradient(135deg, #d97706 0%, #92400e 55%, #451a03 100%);
        color: #fff; text-decoration: none;
        box-shadow: 0 8px 22px rgba(0, 0, 0, .32);
        transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
    }
    .safari-hero__card:hover,
    .safari-hero__card.is-active { border-color: #fbbf24; }
    .safari-hero__card.is-active { transform: translateY(-5px); box-shadow: 0 16px 32px rgba(0, 0, 0, .5); }

    .safari-hero__card > img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
    .safari-hero__card:hover > img,
    .safari-hero__card.is-active > img { transform: scale(1.07); }
    .safari-hero__card::after { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(20, 8, 2, .92), rgba(20, 8, 2, .05) 72%); content: ""; }

    .safari-hero__card-index {
        position: absolute; top: 10px; left: 12px; z-index: 1;
        font-size: 12px; font-weight: 700; letter-spacing: .08em; color: rgba(255, 255, 255, .9);
        text-shadow: 0 1px 6px rgba(0, 0, 0, .7);
    }
    .safari-hero__card-go {
        position: absolute; top: 9px; right: 10px; z-index: 1;
        display: grid; width: 30px; height: 30px; place-items: center;
        border-radius: 50%; background: rgba(255, 255, 255, .92); color: #2b1305;
        opacity: 0; transform: translate(-6px, 6px) scale(.85);
        transition: opacity .25s ease, transform .25s ease;
    }
    .safari-hero__card:hover .safari-hero__card-go,
    .safari-hero__card:focus-visible .safari-hero__card-go,
    .safari-hero__card.is-active .safari-hero__card-go { opacity: 1; transform: none; }

    .safari-hero__card-text { position: relative; z-index: 1; width: 100%; padding: 12px 14px 14px; }
    .safari-hero__card-title { display: block; font-size: 16px; font-weight: 700; line-height: 1.2; text-shadow: 0 1px 8px rgba(0, 0, 0, .6); }
    .safari-hero__card-meta {
        display: inline-block; margin-top: 7px; padding: 3px 9px;
        border-radius: 999px; background: rgba(251, 191, 36, .2); border: 1px solid rgba(251, 191, 36, .45);
        color: #ffe9a8; font-size: 11px; font-weight: 700;
    }
    .safari-hero__progress { position: absolute; right: 0; bottom: 0; left: 0; z-index: 2; height: 3px; background: rgba(255, 255, 255, .25); }
    .safari-hero__progress > span { display: block; height: 100%; background: #fbbf24; transition: width .1s linear; }

    /* ---------- Tablet ---------- */
    @media (max-width: 900px) {
        .safari-hero { height: auto; min-height: calc(100svh - var(--hero-top)); }
        .safari-hero__content { padding: 0 20px; }
        .safari-hero__copy { padding: 30px 0 22px; }
        .safari-hero__cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .safari-hero__card { min-height: 110px; }
        .safari-hero__card-go { opacity: 1; transform: none; }
    }

    /* ---------- Mobile: swipeable card strip keeps the hero on one screen ---------- */
    @media (max-width: 600px) {
        .safari-hero__overlay {
            background:
                linear-gradient(90deg, rgba(18, 7, 2, .55), rgba(18, 7, 2, .25)),
                linear-gradient(0deg, rgba(18, 7, 2, .85), rgba(18, 7, 2, .1) 100%);
        }
        .safari-hero__content { padding: 0 16px; }
        .safari-hero__copy { padding: 24px 0 18px; }
        .safari-hero__badge { margin-bottom: 12px; font-size: 11px; }
        .safari-hero__title { font-size: clamp(26px, 8vw, 34px); }
        .safari-hero__regions { font-size: 13px; margin-top: 10px; }
        .safari-hero__actions { gap: 10px; margin-top: 20px; }
        .safari-hero__button { flex: 1 1 100%; min-height: 52px; }
        .safari-hero__cards {
            display: flex; gap: 10px; margin-inline: -16px; padding: 0 16px 6px;
            overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch;
        }
        .safari-hero__cards::-webkit-scrollbar { display: none; }
        .safari-hero__card { flex: 0 0 64%; min-height: 104px; scroll-snap-align: start; border-radius: 14px; }
        .safari-hero__card-title { font-size: 14px; }
        .safari-hero__dust span:nth-child(n+8) { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .safari-hero__slide.is-active { transform: none; transition: opacity .6s ease; }
        .safari-hero__rise { opacity: 1; animation: none; }
        .safari-hero__badge i { animation: none; }
        .safari-hero__dust { display: none; }
        .safari-hero *, .safari-hero *::before, .safari-hero *::after { transition-duration: .01s !important; }
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
            this.fit();
            window.addEventListener('resize', () => this.fit());
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.paused = true;
            if (this.total > 1) setInterval(() => this.tick(), 100);
        },
        fit() {
            const top = Math.max(0, this.$el.getBoundingClientRect().top + window.scrollY);
            this.$el.style.setProperty('--hero-top', top + 'px');
        },
        tick() {
            if (this.paused || this.hold || document.hidden) return;
            this.elapsed += 100;
            if (this.elapsed >= this.duration) this.go((this.active + 1) % this.total);
        },
        go(i) {
            this.active = i;
            this.elapsed = 0;
            const box = this.$refs.cards, el = box && box.children[i];
            if (el && window.innerWidth <= 600) box.scrollTo({ left: el.offsetLeft - box.offsetLeft - 16, behavior: 'smooth' });
        }
    }"
>
    <img
        class="safari-hero__background"
        src="{{ $heroImage }}"
        alt=""
        width="1920"
        height="1080"
        decoding="async"
        onerror="this.style.display='none'"
    >

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

    <div class="safari-hero__dust" aria-hidden="true">
        @for ($p = 0; $p < 10; $p++)
            <span style="--x: {{ ($p * 37 + 11) % 100 }}%; --s: {{ 2 + ($p % 3) }}px; --t: {{ 9 + ($p % 5) * 2 }}s; --d: -{{ ($p * 1.3) }}s;"></span>
        @endfor
    </div>

    <div class="safari-hero__main">
        <div class="safari-hero__content">
            <div class="safari-hero__copy">
                @if ($eyebrow)
                    <p class="safari-hero__badge safari-hero__rise" style="--delay: .05s"><i aria-hidden="true"></i>{{ $eyebrow }}</p>
                @endif

                @if ($headline)
                    <h1 class="safari-hero__title safari-hero__rise" style="--delay: .15s">
                        {{ $headFirst }}@if ($headAccent) <em>{{ $headAccent }}</em>@endif
                    </h1>
                @endif

                @if (! empty($regions))
                    <ul class="safari-hero__regions safari-hero__rise" style="--delay: .25s" aria-label="Destinations">
                        @foreach ($regions as $region)
                            <li>{{ $region }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="safari-hero__actions safari-hero__rise" style="--delay: .35s">
                    <a href="{{ $toursUrl }}" class="safari-hero__button safari-hero__button--primary">
                        {{ $ctaText ?: 'Browse Tours' }}
                        <span class="safari-hero__arrow" aria-hidden="true">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>

                    @if (function_exists('whatsapp_url'))
                        <a
                            href="{{ whatsapp_url('Hello, I would like to book a desert safari.') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="safari-hero__button safari-hero__button--whatsapp"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            Chat on WhatsApp
                        </a>
                    @endif
                </div>

                @if ($search && \Illuminate\Support\Facades\Route::has('search'))
                    <form action="{{ route('search') }}" method="GET" role="search" class="safari-hero__search safari-hero__rise" style="--delay: .45s">
                        <label for="hero-search" class="sr-only">Search tours</label>
                        <input id="hero-search" type="search" name="q" value="{{ request('q') }}" autocomplete="off" placeholder="Search safaris, quad biking, camping...">
                        <button type="submit">Search</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if ($cards->isNotEmpty())
        <div class="safari-hero__content safari-hero__bottom">
            <div class="safari-hero__bar">
                <span class="safari-hero__bar-label">Explore by category @if ($totalTours > 0)&middot; <b>{{ $totalTours }}</b> {{ \Illuminate\Support\Str::plural('tour', $totalTours) }}@endif</span>

                @if ($cards->count() > 1)
                    <button
                        type="button"
                        class="safari-hero__pause"
                        x-on:click="paused = !paused"
                        :aria-pressed="paused.toString()"
                        :aria-label="paused ? 'Play background slideshow' : 'Pause background slideshow'"
                    >
                        <svg x-show="!paused" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>
                        <svg x-show="paused" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4l13 8-13 8z"/></svg>
                    </button>
                @endif
            </div>

            <div
                class="safari-hero__cards"
                x-ref="cards"
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

                        <span class="safari-hero__card-index" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="safari-hero__card-go" aria-hidden="true">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9"/></svg>
                        </span>

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