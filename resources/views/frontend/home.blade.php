@extends('layouts.frontend')

@push('jsonld')
    <x-seo.jsonld :data="app(\App\Services\SeoService::class)->website()" />
    @if ($faqs->isNotEmpty())
        <x-seo.jsonld :data="app(\App\Services\SeoService::class)->faqPage($faqs)" />
    @endif
@endpush

@section('content')
    {{-- Hero (settings) --}}
    <section class="relative isolate overflow-hidden bg-stone-900">
        @if ($site->hero_image)
            <img src="{{ asset('storage/'.$site->hero_image) }}" alt="" width="1920" height="900"
                 class="absolute inset-0 -z-10 h-full w-full object-cover opacity-60" fetchpriority="high">
        @else
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-amber-700 via-amber-600 to-orange-800"></div>
        @endif

        <div class="mx-auto max-w-7xl px-4 py-24 text-center sm:py-32">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-6xl">{{ $site->hero_title }}</h1>
            @if ($site->hero_subtitle)
                <p class="mx-auto mt-5 max-w-2xl text-lg text-amber-50">{{ $site->hero_subtitle }}</p>
            @endif

            <form action="{{ route('search') }}" method="GET" class="mx-auto mt-8 flex max-w-xl gap-2">
                <input type="search" name="q" placeholder="Search desert safaris, camel rides, camping..." class="field flex-1" aria-label="Search tours">
                <button class="btn btn-primary">Search</button>
            </form>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('tours.index') }}" class="btn btn-outline">{{ $site->hero_cta_text }}</a>
                <a href="{{ whatsapp_url('Hello, I would like to book a desert safari.') }}" target="_blank" rel="noopener" class="btn btn-whatsapp">Chat on WhatsApp</a>
            </div>
        </div>
    </section>

    @if ($trustBadges->isNotEmpty())
        <x-home.trust-badges :badges="$trustBadges" />
    @elseif (! empty($site->trust_badges))
        <x-home.trust-badges :badges="$site->trust_badges" />
    @endif

    <x-home.quick-links :links="$activityLinks" heading="Explore by activity" />
    <x-home.intro :title="$site->home_intro_title" :html="$site->home_intro_html" />

    @if ($spotlight)
        <x-home.spotlight :tour="$spotlight" />
    @endif

    <x-home.categories :categories="$categories" />
    <x-home.featured-tours :tours="$featured" heading="Featured tours" subtitle="Hand-picked desert experiences." :link="route('tours.index')" />
    <x-home.featured-tours :tours="$bestSelling" heading="Best-selling experiences" subtitle="What most of our guests book." />
    <x-home.event-packages :packages="$eventPackages" />

    <x-home.info-grid heading="Our vehicles" subtitle="Comfortable, well-maintained desert vehicles with experienced drivers." :items="$vehicles" route="vehicles.show" />
    <x-home.info-grid heading="Camp categories" subtitle="From simple family camps to private VIP majlis." :items="$campTypes" route="camps.show" />
    <x-home.info-grid heading="Deserts we explore" :items="$deserts" route="deserts.show" />
    <x-home.info-grid heading="Camp facilities" :items="$facilities" route="facilities.show" />
    <x-home.info-grid heading="Desert safari highlights" :items="$highlights" route="highlights.show" name-key="title" />

    <x-home.faq-ask :faqs="$faqs" :tours="$tourOptions" />
    <x-home.gallery :items="$gallery" />
    <x-home.testimonials :reviews="$testimonials" />
    <x-home.blog :posts="$blogs" />
    <x-home.quick-links :links="$popularLinks" heading="Popular searches" />
@endsection