@extends('layouts.frontend')

@php
    $images = collect();

    if ($cover = $tour->getFirstMedia('cover')) {
        $images->push(['large' => $cover->getUrl('large'), 'thumb' => $cover->getUrl('thumb'), 'alt' => $tour->coverAlt()]);
    }

    foreach ($tour->getMedia('gallery') as $media) {
        $images->push(['large' => $media->getUrl('large'), 'thumb' => $media->getUrl('thumb'), 'alt' => (string) ($media->getCustomProperty('alt') ?: $tour->title)]);
    }

    $tabs = ['overview' => 'Overview'];
    if (filled($tour->description)) { $tabs['description'] = 'Description'; }
    if ($tour->itineraryItems->isNotEmpty()) { $tabs['itinerary'] = 'Itinerary'; }
    if ($tour->additionalInfos->isNotEmpty()) { $tabs['info'] = 'Additional info'; }
    if ($tour->addons->isNotEmpty()) { $tabs['addons'] = 'Add-ons'; }
    if ($tour->inclusions->isNotEmpty() || $tour->exclusions->isNotEmpty()) { $tabs['included'] = 'Included / excluded'; }
    $tabs['cancellation'] = 'Cancellation policy';
    $tabs['faq'] = 'FAQ & questions';
    $tabs['reviews'] = 'Reviews ('.$tour->reviews_count_cached.')';

    $startTab = array_key_exists((string) request('tab'), $tabs) ? (string) request('tab') : 'overview';
    $duration = rtrim(rtrim(number_format((float) $tour->duration_hours, 1), '0'), '.');
@endphp

@section('title', $tour->meta_title ?: $tour->title)
@section('meta_description', $tour->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($tour->short_description ?: $tour->description)), 155))
@section('canonical', $tour->canonical ?: route('tours.show', $tour->slug))
@section('og_type', 'product')

@push('jsonld')
    <x-seo.jsonld :data="app(\App\Services\SeoService::class)->tour($tour, $images->pluck('large')->all())" />
    @if ($tour->faqs->isNotEmpty())
        <x-seo.jsonld :data="app(\App\Services\SeoService::class)->faqPage($tour->faqs)" />
    @endif
@endpush
@section('og_image', $tour->og_image ?: ($images->first()['large'] ?? ''))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    <x-breadcrumbs :items="[['All activities', route('tours.index')], [$tour->category->name, route('categories.show', $tour->category->slug)], [$tour->title, null]]" />

    <x-flash class="mt-4" />

    <div class="mt-4 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">

            {{-- Gallery --}}
            <div x-data="{ i: 0, images: @js($images->values()) }">
                <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-stone-100">
                    @if ($images->isNotEmpty())
                        <img src="{{ $images->first()['large'] }}" :src="images[i].large" :alt="images[i].alt" alt="{{ $images->first()['alt'] }}"
                             class="h-full w-full object-cover" fetchpriority="high">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-amber-200 via-orange-200 to-amber-400"></div>
                    @endif

                    <div class="absolute left-3 top-3 flex flex-wrap gap-1">
                        @foreach ($tour->badges ?? [] as $badge)
                            <span class="rounded-full bg-amber-600 px-3 py-1 text-xs font-semibold text-white">{{ $badge->label() }}</span>
                        @endforeach
                        @if ($tour->has_discount)<span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">-{{ $tour->discount_percent }}%</span>@endif
                    </div>
                </div>

                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($images as $n => $image)
                            <button type="button" @click="i = {{ $n }}" :class="i === {{ $n }} ? 'ring-2 ring-amber-500' : 'opacity-70'"
                                    class="h-16 w-24 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ $image['thumb'] }}" alt="" loading="lazy" width="96" height="64" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Title and quick facts --}}
            <header class="mt-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-amber-700">
                    <a href="{{ route('categories.show', $tour->category->slug) }}">{{ $tour->category->name }}</a>
                    @if ($tour->desert) &middot; <a href="{{ route('deserts.show', $tour->desert->slug) }}">{{ $tour->desert->name }}</a>@endif
                </p>
                <h1 class="mt-1 text-3xl font-extrabold leading-tight text-stone-900 sm:text-4xl">{{ $tour->title }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-stone-600">
                    <x-rating :value="$tour->rating_cached" :count="$tour->reviews_count_cached" />
                    <span>{{ $duration }} hours</span>
                    @if ($tour->max_group_size)<span>Up to {{ $tour->max_group_size }} guests</span>@endif
                    @if ($tour->min_age)<span>Age {{ $tour->min_age }}+</span>@endif
                </div>
                <ul class="mt-3 flex flex-wrap gap-2 text-xs">
                    @foreach (['has_dinner' => 'Dinner included', 'has_breakfast' => 'Breakfast included', 'has_guide' => 'Local guide', 'has_transfer' => 'Pick and drop'] as $flag => $label)
                        @if ($tour->{$flag})<li class="rounded-full bg-green-50 px-3 py-1 font-medium text-green-800">&#10003; {{ $label }}</li>@endif
                    @endforeach
                </ul>
            </header>

            {{-- Tabs --}}
            <div x-data="{ tab: @js($startTab) }" class="mt-8" id="details">
                <div class="flex gap-1 overflow-x-auto border-b border-stone-200" role="tablist">
                    @foreach ($tabs as $key => $label)
                        <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'border-amber-600 text-amber-700' : 'border-transparent text-stone-600 hover:text-stone-900'"
                                class="-mb-px whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="pt-6">
                    {{-- Overview --}}
                    <section x-show="tab === 'overview'" class="space-y-6">
                        @if ($tour->short_description)<p class="text-lg leading-relaxed text-stone-700">{{ $tour->short_description }}</p>@endif

                        @if ($tour->highlights->isNotEmpty())
                            <div>
                                <h2 class="mb-2 text-lg font-bold text-stone-900">Highlights</h2>
                                <ul class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($tour->highlights as $h)<li class="flex gap-2 text-sm text-stone-700"><span class="text-green-600">&#10003;</span>{{ $h->text }}</li>@endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($tour->campTypes->isNotEmpty() || $tour->vehicles->isNotEmpty())
                            <div class="grid gap-4 sm:grid-cols-2">
                                @if ($tour->campTypes->isNotEmpty())
                                    <div class="rounded-xl bg-white p-4 ring-1 ring-stone-200">
                                        <h3 class="font-semibold text-stone-900">Camp options</h3>
                                        <ul class="mt-1 text-sm text-stone-700">@foreach ($tour->campTypes as $c)<li><a href="{{ route('camps.show', $c->slug) }}" class="hover:text-amber-700">{{ $c->name }}</a></li>@endforeach</ul>
                                    </div>
                                @endif
                                @if ($tour->vehicles->isNotEmpty())
                                    <div class="rounded-xl bg-white p-4 ring-1 ring-stone-200">
                                        <h3 class="font-semibold text-stone-900">Vehicle options</h3>
                                        <ul class="mt-1 text-sm text-stone-700">@foreach ($tour->vehicles as $v)<li><a href="{{ route('vehicles.show', $v->slug) }}" class="hover:text-amber-700">{{ $v->name }}</a></li>@endforeach</ul>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </section>

                    {{-- Description --}}
                    @isset($tabs['description'])
                        <section x-show="tab === 'description'" x-cloak class="rich">{!! \App\Support\Html::clean($tour->description) !!}</section>
                    @endisset

                    {{-- Itinerary --}}
                    @isset($tabs['itinerary'])
                        <section x-show="tab === 'itinerary'" x-cloak>
                            <ol class="relative space-y-6 border-l-2 border-amber-200 pl-6">
                                @foreach ($tour->itineraryItems as $stop)
                                    <li class="relative">
                                        <span class="absolute -left-[1.95rem] top-1 h-3 w-3 rounded-full bg-amber-500"></span>
                                        @if ($stop->time_label)<p class="text-sm font-semibold text-amber-700">{{ $stop->time_label }}</p>@endif
                                        <h3 class="font-bold text-stone-900">{{ $stop->title }}</h3>
                                        @if ($stop->description)<p class="mt-1 text-sm text-stone-700">{{ $stop->description }}</p>@endif
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endisset

                    {{-- Additional info --}}
                    @isset($tabs['info'])
                        <section x-show="tab === 'info'" x-cloak class="space-y-4">
                            @foreach ($tour->additionalInfos as $info)
                                <div class="rounded-xl bg-white p-4 ring-1 ring-stone-200">
                                    <h3 class="font-bold text-stone-900">{{ $info->title }}</h3>
                                    <p class="mt-1 whitespace-pre-line text-sm text-stone-700">{{ $info->content }}</p>
                                </div>
                            @endforeach
                        </section>
                    @endisset

                    {{-- Add-ons --}}
                    @isset($tabs['addons'])
                        <section x-show="tab === 'addons'" x-cloak class="grid gap-3 sm:grid-cols-2">
                            @foreach ($tour->addons as $addon)
                                <div class="rounded-xl bg-white p-4 ring-1 ring-stone-200">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-bold text-stone-900">{{ $addon->name }}</h3>
                                        <span class="whitespace-nowrap text-sm font-semibold text-amber-700">{{ money($addon->pivot->price_override ?? $addon->price) }}</span>
                                    </div>
                                    @if ($addon->description)<p class="mt-1 text-sm text-stone-600">{{ $addon->description }}</p>@endif
                                </div>
                            @endforeach
                            <p class="text-xs text-stone-500 sm:col-span-2">Choose add-ons in the booking box.</p>
                        </section>
                    @endisset

                    {{-- Included / excluded --}}
                    @isset($tabs['included'])
                        <section x-show="tab === 'included'" x-cloak class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <h3 class="mb-2 font-bold text-stone-900">Included</h3>
                                <ul class="space-y-1 text-sm text-stone-700">@foreach ($tour->inclusions as $item)<li class="flex gap-2"><span class="text-green-600">&#10003;</span>{{ $item->text }}</li>@endforeach</ul>
                            </div>
                            <div>
                                <h3 class="mb-2 font-bold text-stone-900">Not included</h3>
                                <ul class="space-y-1 text-sm text-stone-700">@foreach ($tour->exclusions as $item)<li class="flex gap-2"><span class="text-red-500">&times;</span>{{ $item->text }}</li>@endforeach</ul>
                            </div>
                        </section>
                    @endisset

                    {{-- Cancellation --}}
                    <section x-show="tab === 'cancellation'" x-cloak class="space-y-3">
                        @if (filled($tour->cancellation_policy))
                            <div class="rich">{!! \App\Support\Html::clean($tour->cancellation_policy) !!}</div>
                        @else
                            <p class="text-stone-700">
                                Free cancellation up to {{ config('booking.free_cancellation_hours', 24) }} hours before the start time.
                                Later cancellations may be charged {{ rtrim(rtrim(number_format((float) config('booking.late_cancellation_fee_percent', 50), 1), '0'), '.') }}% of the booking total.
                            </p>
                        @endif
                        <p class="text-sm text-stone-500">See our full <a href="{{ route('refund') }}" class="text-amber-700 underline">cancellation and refund policy</a>.</p>
                    </section>

                    {{-- FAQ and questions --}}
                    <section x-show="tab === 'faq'" x-cloak class="space-y-8">
                        @if ($tour->faqs->isNotEmpty())
                            <x-faq-accordion :items="$tour->faqs" />
                        @endif

                        <div>
                            <h3 class="mb-3 text-lg font-bold text-stone-900">Questions from other guests</h3>
                            @forelse ($tour->approvedQuestions as $q)
                                <div class="mb-3 rounded-xl bg-white p-4 ring-1 ring-stone-200">
                                    <p class="font-semibold text-stone-900">{{ $q->question }}</p>
                                    <p class="text-xs text-stone-500">Asked by {{ $q->name }}</p>
                                    @if ($q->answer)<p class="mt-2 rounded bg-amber-50 p-3 text-sm text-stone-700">{{ $q->answer }}</p>@endif
                                </div>
                            @empty
                                <p class="text-sm text-stone-500">No questions yet. Be the first to ask.</p>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('questions.store') }}" class="space-y-3 rounded-2xl bg-white p-5 ring-1 ring-stone-200">
                            @csrf
                            <h3 class="font-bold text-stone-900">Ask about this tour</h3>
                            <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input name="name" type="text" value="{{ old('name') }}" placeholder="Your name" required class="field" aria-label="Your name">
                                <input name="email" type="email" value="{{ old('email') }}" placeholder="Email (not shown)" required class="field" aria-label="Email">
                            </div>
                            <textarea name="question" rows="3" placeholder="Your question" required class="field" aria-label="Your question">{{ old('question') }}</textarea>
                            @if ($errors->hasAny(['name', 'email', 'question']))<p class="text-xs text-red-600">{{ $errors->first() }}</p>@endif
                            <button class="btn btn-primary">Send question</button>
                        </form>
                    </section>

                    {{-- Reviews --}}
                    <section x-show="tab === 'reviews'" x-cloak class="space-y-8">
                        <div>
                            @forelse ($tour->approvedReviews as $review)
                                <figure class="mb-4 rounded-xl bg-white p-4 ring-1 ring-stone-200">
                                    <p class="text-amber-500" aria-label="{{ $review->rating }} out of 5">{{ str_repeat('★', $review->rating) }}<span class="text-stone-300">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                                    @if ($review->title)<p class="mt-1 font-semibold text-stone-900">{{ $review->title }}</p>@endif
                                    <blockquote class="mt-1 text-sm text-stone-700">{{ $review->comment }}</blockquote>
                                    <figcaption class="mt-2 text-xs text-stone-500">{{ $review->name }}{{ $review->country ? ', '.$review->country : '' }}{{ $review->visited_on ? ' | visited '.$review->visited_on->format('M Y') : '' }}</figcaption>
                                </figure>
                            @empty
                                <p class="text-sm text-stone-500">No reviews yet. Share your experience after your trip.</p>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('reviews.store', $tour->slug) }}" class="space-y-3 rounded-2xl bg-white p-5 ring-1 ring-stone-200">
                            @csrf
                            <h3 class="font-bold text-stone-900">Write a review</h3>
                            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <input name="name" type="text" value="{{ old('name') }}" placeholder="Your name" required class="field" aria-label="Your name">
                                <input name="country" type="text" value="{{ old('country') }}" placeholder="Country" class="field" aria-label="Country">
                                <select name="rating" required class="field" aria-label="Rating">
                                    <option value="">Rating</option>
                                    @foreach ([5, 4, 3, 2, 1] as $r)<option value="{{ $r }}" @selected(old('rating') == $r)>{{ $r }} {{ \Illuminate\Support\Str::plural('star', $r) }}</option>@endforeach
                                </select>
                            </div>
                            <input name="title" type="text" value="{{ old('title') }}" placeholder="Headline (optional)" class="field" aria-label="Headline">
                            <textarea name="comment" rows="4" placeholder="Tell us about your trip" required class="field" aria-label="Your review">{{ old('comment') }}</textarea>
                            <div>
                                <label class="text-xs text-stone-500" for="visited_on">Date of visit (optional)</label>
                                <input id="visited_on" name="visited_on" type="date" max="{{ today()->toDateString() }}" value="{{ old('visited_on') }}" class="field">
                            </div>
                            @if ($errors->hasAny(['name', 'rating', 'comment', 'visited_on']))<p class="text-xs text-red-600">{{ $errors->first() }}</p>@endif
                            <button class="btn btn-primary">Submit review</button>
                            <p class="text-xs text-stone-500">Reviews are published after a quick check.</p>
                        </form>
                    </section>
                </div>
            </div>
        </div>

        {{-- Booking box --}}
        <aside id="book" class="lg:col-span-1">
            @include('frontend.tours._booking-widget')
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-16">
            <h2 class="mb-6 text-2xl font-extrabold text-stone-900">You might also like</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">@foreach ($related as $item)<x-tour-card :tour="$item" />@endforeach</div>
        </section>
    @endif
</div>

{{-- Mobile booking bar --}}
<div class="fixed inset-x-0 bottom-0 z-30 flex items-center justify-between gap-3 border-t border-stone-200 bg-white px-4 py-3 shadow-lg lg:hidden">
    <x-price-badge :tour="$tour" />
    <a href="#book" class="btn btn-primary">Book Now</a>
</div>
@endsection