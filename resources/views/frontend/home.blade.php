@extends('layouts.frontend')

@push('jsonld')
    <x-seo.jsonld :data="app(\App\Services\SeoService::class)->website()" />

    @if ($faqs->isNotEmpty())
        <x-seo.jsonld :data="app(\App\Services\SeoService::class)->faqPage($faqs)" />
    @endif
@endpush

@section('content')
    {{-- Hero --}}
    <x-home.hero
        :title="$site->hero_title"
        :subtitle="$site->hero_subtitle"
        :eyebrow="$site->hero_eyebrow ?? null"
        :image-url="filled($site->hero_image) ? asset('storage/'.ltrim($site->hero_image, '/')) : null"
        :cta-text="$site->hero_cta_text ?? null"
    />

    {{-- Trust badges: raise above the hero and keep them in normal document flow --}}
    @if ($trustBadges->isNotEmpty())
        <div class="relative z-10 mx-auto -mt-5 max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-home.trust-badges :badges="$trustBadges" />
        </div>
    @elseif (! empty($site->trust_badges))
        <div class="relative z-10 mx-auto -mt-5 max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-home.trust-badges :badges="$site->trust_badges" />
        </div>
    @endif

    {{-- Temporarily hidden; restore this component call to re-enable Browse by Activity. --}}
    {{--
    <x-home.quick-links :links="$activityLinks" heading="Explore by activity" />
    --}}

    {{-- Introduction --}}
    <x-home.intro :title="$site->home_intro_title" :html="$site->home_intro_html" />

    {{-- Featured spotlight --}}
    @if ($spotlight)
        <x-home.spotlight :tour="$spotlight" />
    @endif

   

    {{-- Featured and popular tours --}}
    <x-home.featured-tours
        :tours="$featured"
        heading="Featured tours"
        subtitle="Hand-picked desert experiences for your next adventure."
        :link="route('tours.index')"
    />

     {{-- Browse tour categories --}}
    <x-home.categories :categories="$categories" />

    
    {{-- Supporting information --}}
    <x-home.info-grid
        heading="Our vehicles"
        subtitle="Comfortable, well-maintained desert vehicles with experienced drivers."
        :items="$vehicles"
        route="vehicles.show"
    />

    <x-home.featured-tours
        :tours="$bestSelling"
        heading="Best-selling experiences"
        subtitle="Explore popular experiences for your next getaway."
    />

    {{-- Group experiences --}}
    <x-home.event-packages :packages="$eventPackages" />


    <x-home.info-grid
        heading="Camp categories"
        subtitle="Discover traditional desert camps and premium private experiences."
        :items="$campTypes"
        route="camps.show"
    />

    <x-home.info-grid
        heading="Deserts we explore"
        :items="$deserts"
        route="deserts.show"
    />

    <x-home.info-grid
        heading="Camp facilities"
        :items="$facilities"
        route="facilities.show"
    />

    <x-home.info-grid
        heading="Desert safari highlights"
        :items="$highlights"
        route="highlights.show"
        name-key="title"
    />

    {{-- FAQs, gallery and social proof --}}
    <x-home.faq-ask :faqs="$faqs" :tours="$tourOptions" />
    <x-home.gallery :items="$gallery" />
    <x-home.testimonials :reviews="$testimonials" />
    <x-home.blog :posts="$blogs" />

    {{-- Popular searches --}}
    <x-home.quick-links :links="$popularLinks" heading="Popular searches" />
@endsection