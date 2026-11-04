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
    if (filled($tour->description)) { $tabs['description'] = 'About this tour'; }
    if ($tour->itineraryItems->isNotEmpty()) { $tabs['itinerary'] = 'Tour Plan'; }
    if ($tour->additionalInfos->isNotEmpty()) { $tabs['info'] = 'Good to Know'; }
    if ($tour->addons->isNotEmpty()) { $tabs['addons'] = 'Extras'; }
    if ($tour->inclusions->isNotEmpty() || $tour->exclusions->isNotEmpty()) { $tabs['included'] = "What's Included"; }
    $tabs['cancellation'] = 'Cancellation & Refund';
    $tabs['faq'] = 'Questions & Answers';
    $tabs['reviews'] = 'Reviews ('.$tour->reviews_count_cached.')';

    $startTab = array_key_exists((string) request('tab'), $tabs) ? (string) request('tab') : 'overview';
    $duration = rtrim(rtrim(number_format((float) $tour->duration_hours, 1), '0'), '.');

    $inputClass = 'block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 placeholder-stone-400 shadow-sm transition focus:border-[#8B2014] focus:outline-none focus:ring-2 focus:ring-[#8B2014]/20';
    $btnClass = 'inline-flex min-h-[46px] items-center justify-center rounded-xl bg-[#8B2014] px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-[#741a10] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2';
    $cardClass = 'rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200';
    $checkIcon = '<svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>';
    $crossIcon = '<svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 5.3a1 1 0 011.4 0L10 7.6l2.3-2.3a1 1 0 111.4 1.4L11.4 9l2.3 2.3a1 1 0 01-1.4 1.4L10 10.4l-2.3 2.3a1 1 0 01-1.4-1.4L8.6 9 6.3 6.7a1 1 0 010-1.4z"/></svg>';
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
<div class="border-b border-stone-200 bg-gradient-to-b from-[#fbf3ea] to-white">
    <div class="mx-auto max-w-7xl px-4 pb-6 pt-6">
        <x-breadcrumbs :items="[['All activities', route('tours.index')], [$tour->category->name, route('categories.show', $tour->category->slug)], [$tour->title, null]]" />
    </div>
</div>

<div class="mx-auto max-w-7xl px-4 pb-28 pt-6 lg:pb-14">

    <x-flash class="mb-4" />

    {{-- Title block --}}
    <header class="mb-6">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('categories.show', $tour->category->slug) }}"
               class="inline-flex items-center rounded-full bg-[#8B2014]/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#8B2014] transition hover:bg-[#8B2014]/20">{{ $tour->category->name }}</a>
            @if ($tour->desert)
                <a href="{{ route('deserts.show', $tour->desert->slug) }}"
                   class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-stone-700 transition hover:bg-stone-200">{{ $tour->desert->name }}</a>
            @endif
        </div>

        <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-4xl lg:text-5xl">{{ $tour->title }}</h1>

        <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-stone-600">
            <x-rating :value="$tour->rating_cached" :count="$tour->reviews_count_cached" />
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4 text-[#8B2014]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.29.71l2.5 2.5a1 1 0 001.42-1.42L11 9.59V6z" clip-rule="evenodd"/></svg>
                {{ $duration }} hours
            </span>
            @if ($tour->max_group_size)
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-[#8B2014]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M13 7a3 3 0 11-6 0 3 3 0 016 0zM4 16a6 6 0 1112 0v1H4v-1z"/></svg>
                    Up to {{ $tour->max_group_size }} guests
                </span>
            @endif
            @if ($tour->min_age)
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-[#8B2014]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5a1 1 0 112 0 1 1 0 01-2 0zm1-7a1 1 0 00-1 1v3a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Age {{ $tour->min_age }}+
                </span>
            @endif
        </div>
    </header>

    <div class="grid gap-8 lg:grid-cols-3 lg:items-start">
        <div class="min-w-0 lg:col-span-2">

            {{-- Gallery --}}
            <div x-data="{ i: 0, images: @js($images->values()), next() { this.i = (this.i + 1) % this.images.length }, prev() { this.i = (this.i - 1 + this.images.length) % this.images.length } }"
                 @keydown.arrow-right.window="images.length > 1 && next()"
                 @keydown.arrow-left.window="images.length > 1 && prev()">
                <div class="relative aspect-[16/10] overflow-hidden rounded-3xl bg-stone-100 shadow-sm ring-1 ring-stone-200">
                    @if ($images->isNotEmpty())
                        <img src="{{ $images->first()['large'] }}" :src="images[i].large" :alt="images[i].alt" alt="{{ $images->first()['alt'] }}"
                             class="h-full w-full object-cover" fetchpriority="high">
                    @else
                        <div class="h-full w-full bg-gradient-to-br from-amber-200 via-orange-200 to-amber-400"></div>
                    @endif

                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/30 to-transparent" aria-hidden="true"></div>

                    <div class="absolute left-4 top-4 flex flex-wrap gap-1.5">
                        @foreach ($tour->badges ?? [] as $badge)
                            <span class="rounded-full bg-[#8B2014] px-3 py-1 text-xs font-semibold text-white shadow">{{ $badge->label() }}</span>
                        @endforeach
                        @if ($tour->has_discount)
                            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white shadow">-{{ $tour->discount_percent }}%</span>
                        @endif
                    </div>

                    @if ($images->count() > 1)
                        <button type="button" @click="prev()" aria-label="Previous photo"
                                class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-stone-800 shadow transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.7 5.3a1 1 0 010 1.4L9.4 10l3.3 3.3a1 1 0 01-1.4 1.4l-4-4a1 1 0 010-1.4l4-4a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        </button>
                        <button type="button" @click="next()" aria-label="Next photo"
                                class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-stone-800 shadow transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.3 14.7a1 1 0 010-1.4L10.6 10 7.3 6.7a1 1 0 111.4-1.4l4 4a1 1 0 010 1.4l-4 4a1 1 0 01-1.4 0z" clip-rule="evenodd"/></svg>
                        </button>
                        <span class="absolute bottom-3 right-3 rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white" x-text="(i + 1) + ' / ' + images.length">1 / {{ $images->count() }}</span>
                    @endif
                </div>

                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                        @foreach ($images as $n => $image)
                            <button type="button" @click="i = {{ $n }}" aria-label="Show photo {{ $n + 1 }}"
                                    :class="i === {{ $n }} ? 'ring-2 ring-[#8B2014] ring-offset-2' : 'opacity-70 hover:opacity-100'"
                                    class="h-16 w-24 shrink-0 overflow-hidden rounded-xl transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014]">
                                <img src="{{ $image['thumb'] }}" alt="" loading="lazy" width="96" height="64" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Quick facts --}}
            @php
                $flags = collect(['has_dinner' => 'Dinner included', 'has_breakfast' => 'Breakfast included', 'has_guide' => 'Local tour guide', 'has_transfer' => 'Pick and drop'])
                    ->filter(fn ($label, $flag) => $tour->{$flag});
            @endphp
            @if ($flags->isNotEmpty())
                <ul class="mt-5 flex flex-wrap gap-2">
                    @foreach ($flags as $label)
                        <li class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3.5 py-1.5 text-sm font-medium text-emerald-800 ring-1 ring-emerald-100">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                            {{ $label }}
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Tabs --}}
            <div x-data="{ tab: @js($startTab) }" class="mt-8" id="details">
                <div class="sticky top-0 z-20 -mx-4 border-b border-stone-200 bg-white/95 px-4 backdrop-blur sm:mx-0 sm:rounded-t-2xl sm:px-0">
                    <div class="flex gap-1 overflow-x-auto" role="tablist">
                        @foreach ($tabs as $key => $label)
                            <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                                    :class="tab === '{{ $key }}' ? 'border-[#8B2014] text-[#8B2014]' : 'border-transparent text-stone-600 hover:text-stone-900'"
                                    class="-mb-px whitespace-nowrap border-b-2 px-4 py-3.5 text-sm font-semibold transition focus:outline-none focus-visible:bg-stone-50">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="pt-7">
                    {{-- Overview --}}
                    <section x-show="tab === 'overview'" class="space-y-8">
                        @if ($tour->short_description)
                            <p class="text-lg leading-relaxed text-stone-700">{{ $tour->short_description }}</p>
                        @endif

                        @if ($tour->highlights->isNotEmpty())
                            <div class="{{ $cardClass }}">
                                <h2 class="mb-4 text-lg font-bold text-stone-900">Main Attractions</h2>
                                <ul class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($tour->highlights as $h)
                                        <li class="flex gap-2.5 text-sm text-stone-700">{!! $checkIcon !!}<span>{{ $h->text }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($tour->campTypes->isNotEmpty() || $tour->vehicles->isNotEmpty())
                            <div class="grid gap-4 sm:grid-cols-2">
                                @if ($tour->campTypes->isNotEmpty())
                                    <div class="{{ $cardClass }}">
                                        <h3 class="font-bold text-stone-900">Camp choices</h3>
                                        <ul class="mt-3 space-y-2 text-sm">
                                            @foreach ($tour->campTypes as $c)
                                                <li><a href="{{ route('camps.show', $c->slug) }}" class="inline-flex items-center gap-1.5 text-stone-700 transition hover:text-[#8B2014]"><span class="h-1.5 w-1.5 rounded-full bg-[#8B2014]"></span>{{ $c->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if ($tour->vehicles->isNotEmpty())
                                    <div class="{{ $cardClass }}">
                                        <h3 class="font-bold text-stone-900">Vehicle choices</h3>
                                        <ul class="mt-3 space-y-2 text-sm">
                                            @foreach ($tour->vehicles as $v)
                                                <li><a href="{{ route('vehicles.show', $v->slug) }}" class="inline-flex items-center gap-1.5 text-stone-700 transition hover:text-[#8B2014]"><span class="h-1.5 w-1.5 rounded-full bg-[#8B2014]"></span>{{ $v->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </section>

                    {{-- Description --}}
                    @isset($tabs['description'])
                        <section x-show="tab === 'description'" x-cloak class="rich {{ $cardClass }}">{!! \App\Support\Html::clean($tour->description) !!}</section>
                    @endisset

                    {{-- Tour Plan --}}
                    @isset($tabs['itinerary'])
                        <section x-show="tab === 'itinerary'" x-cloak>
                            <ol class="relative space-y-5 border-l-2 border-[#8B2014]/20 pl-8">
                                @foreach ($tour->itineraryItems as $stop)
                                    <li class="relative">
                                        <span class="absolute -left-[2.6rem] top-4 flex h-5 w-5 items-center justify-center rounded-full bg-[#8B2014] ring-4 ring-white"></span>
                                        <div class="{{ $cardClass }}">
                                            @if ($stop->time_label)
                                                <p class="text-xs font-semibold uppercase tracking-wider text-[#8B2014]">{{ $stop->time_label }}</p>
                                            @endif
                                            <h3 class="mt-0.5 font-bold text-stone-900">{{ $stop->title }}</h3>
                                            @if ($stop->description)
                                                <p class="mt-1.5 text-sm leading-relaxed text-stone-600">{{ $stop->description }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endisset

                    {{-- Good to Know --}}
                    @isset($tabs['info'])
                        <section x-show="tab === 'info'" x-cloak class="space-y-4">
                            @foreach ($tour->additionalInfos as $info)
                                <div class="{{ $cardClass }}">
                                    <h3 class="font-bold text-stone-900">{{ $info->title }}</h3>
                                    <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-stone-600">{{ $info->content }}</p>
                                </div>
                            @endforeach
                        </section>
                    @endisset

                    {{-- Extras --}}
                    @isset($tabs['addons'])
                        <section x-show="tab === 'addons'" x-cloak class="grid gap-4 sm:grid-cols-2">
                            @foreach ($tour->addons as $addon)
                                <div class="{{ $cardClass }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="font-bold text-stone-900">{{ $addon->name }}</h3>
                                        <span class="whitespace-nowrap rounded-full bg-[#8B2014]/10 px-2.5 py-1 text-sm font-semibold text-[#8B2014]">{{ money($addon->pivot->price_override ?? $addon->price) }}</span>
                                    </div>
                                    @if ($addon->description)
                                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $addon->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                            <p class="text-xs text-stone-500 sm:col-span-2">You can choose extras in the booking box.</p>
                        </section>
                    @endisset

                    {{-- Included / excluded --}}
                    @isset($tabs['included'])
                        <section x-show="tab === 'included'" x-cloak class="grid gap-4 sm:grid-cols-2">
                            <div class="{{ $cardClass }}">
                                <h3 class="mb-3 font-bold text-stone-900">What is included</h3>
                                <ul class="space-y-2.5 text-sm text-stone-700">
                                    @foreach ($tour->inclusions as $item)
                                        <li class="flex gap-2.5">{!! $checkIcon !!}<span>{{ $item->text }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="{{ $cardClass }}">
                                <h3 class="mb-3 font-bold text-stone-900">What is not included</h3>
                                <ul class="space-y-2.5 text-sm text-stone-700">
                                    @foreach ($tour->exclusions as $item)
                                        <li class="flex gap-2.5">{!! $crossIcon !!}<span>{{ $item->text }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endisset

                    {{-- Cancellation --}}
                    <section x-show="tab === 'cancellation'" x-cloak class="{{ $cardClass }} space-y-3">
                        @if (filled($tour->cancellation_policy))
                            <div class="rich">{!! \App\Support\Html::clean($tour->cancellation_policy) !!}</div>
                        @else
                            <p class="leading-relaxed text-stone-700">
                                Free cancellation up to {{ config('booking.free_cancellation_hours', 24) }} hours before the tour starts.
                                Later cancellations may be charged {{ rtrim(rtrim(number_format((float) config('booking.late_cancellation_fee_percent', 50), 1), '0'), '.') }}% of the booking total.
                            </p>
                        @endif
                        <p class="text-sm text-stone-500">See our full <a href="{{ route('refund') }}" class="font-semibold text-[#8B2014] underline underline-offset-2">cancellation and refund policy</a>.</p>
                    </section>

                    {{-- FAQ and questions --}}
                    <section x-show="tab === 'faq'" x-cloak class="space-y-8">
                        @if ($tour->faqs->isNotEmpty())
                            <x-faq-accordion :items="$tour->faqs" />
                        @endif

                        <div>
                            <h3 class="mb-4 text-lg font-bold text-stone-900">Questions from other visitors</h3>
                            @forelse ($tour->approvedQuestions as $q)
                                <div class="{{ $cardClass }} mb-3">
                                    <p class="font-semibold text-stone-900">{{ $q->question }}</p>
                                    <p class="mt-0.5 text-xs text-stone-500">Asked by {{ $q->name }}</p>
                                    @if ($q->answer)
                                        <p class="mt-3 rounded-xl bg-[#fbf3ea] p-3.5 text-sm leading-relaxed text-stone-700">{{ $q->answer }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-stone-500">No questions yet. Be the first to ask.</p>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('questions.store') }}" class="{{ $cardClass }} space-y-4">
                            @csrf
                            <h3 class="text-lg font-bold text-stone-900">Ask about this tour</h3>
                            <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input name="name" type="text" value="{{ old('name') }}" placeholder="Your name" required class="{{ $inputClass }}" aria-label="Your name">
                                <input name="email" type="email" value="{{ old('email') }}" placeholder="Email (will not be shown)" required class="{{ $inputClass }}" aria-label="Email">
                            </div>
                            <textarea name="question" rows="3" placeholder="Your question" required class="{{ $inputClass }}" aria-label="Your question">{{ old('question') }}</textarea>
                            @if ($errors->hasAny(['name', 'email', 'question']))
                                <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
                            @endif
                            <button class="{{ $btnClass }}">Send question</button>
                        </form>
                    </section>

                    {{-- Reviews --}}
                    <section x-show="tab === 'reviews'" x-cloak class="space-y-8">
                        <div>
                            @forelse ($tour->approvedReviews as $review)
                                <figure class="{{ $cardClass }} mb-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-lg leading-none text-amber-500" aria-label="{{ $review->rating }} out of 5">{{ str_repeat('★', $review->rating) }}<span class="text-stone-300">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                                        @if ($review->visited_on)
                                            <span class="text-xs text-stone-500">Visited {{ $review->visited_on->format('M Y') }}</span>
                                        @endif
                                    </div>
                                    @if ($review->title)
                                        <p class="mt-3 font-bold text-stone-900">{{ $review->title }}</p>
                                    @endif
                                    <blockquote class="mt-1.5 text-sm leading-relaxed text-stone-700">{{ $review->comment }}</blockquote>
                                    <figcaption class="mt-3 flex items-center gap-2 text-xs text-stone-500">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#8B2014]/10 text-xs font-bold text-[#8B2014]">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->name, 0, 1)) }}</span>
                                        <span>{{ $review->name }}{{ $review->country ? ', '.$review->country : '' }}</span>
                                    </figcaption>
                                </figure>
                            @empty
                                <p class="text-sm text-stone-500">No reviews yet. Share your experience after your trip.</p>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('reviews.store', $tour->slug) }}" class="{{ $cardClass }} space-y-4">
                            @csrf
                            <h3 class="text-lg font-bold text-stone-900">Write a review</h3>
                            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <input name="name" type="text" value="{{ old('name') }}" placeholder="Your name" required class="{{ $inputClass }}" aria-label="Your name">
                                <input name="country" type="text" value="{{ old('country') }}" placeholder="Country" class="{{ $inputClass }}" aria-label="Country">
                                <select name="rating" required class="{{ $inputClass }}" aria-label="Rating">
                                    <option value="">Rating</option>
                                    @foreach ([5, 4, 3, 2, 1] as $r)
                                        <option value="{{ $r }}" @selected(old('rating') == $r)>{{ $r }} {{ \Illuminate\Support\Str::plural('star', $r) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input name="title" type="text" value="{{ old('title') }}" placeholder="Review title (optional)" class="{{ $inputClass }}" aria-label="Review title">
                            <textarea name="comment" rows="4" placeholder="Tell us about your trip" required class="{{ $inputClass }}" aria-label="Your review">{{ old('comment') }}</textarea>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500" for="visited_on">Date of visit (optional)</label>
                                <input id="visited_on" name="visited_on" type="date" max="{{ today()->toDateString() }}" value="{{ old('visited_on') }}" class="{{ $inputClass }} sm:max-w-xs">
                            </div>
                            @if ($errors->hasAny(['name', 'rating', 'comment', 'visited_on']))
                                <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-4">
                                <button class="{{ $btnClass }}">Submit review</button>
                                <p class="text-xs text-stone-500">Reviews are published after a quick check.</p>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>

        {{-- Booking box --}}
        <aside id="book" class="scroll-mt-24 lg:col-span-1">
            <div class="lg:sticky lg:top-24">
                @include('frontend.tours._booking-widget')
            </div>
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-16 border-t border-stone-200 pt-12">
            <div class="mb-6 flex items-end justify-between gap-4">
                <h2 class="text-2xl font-extrabold tracking-tight text-stone-900 sm:text-3xl">You might also like</h2>
                <a href="{{ route('categories.show', $tour->category->slug) }}" class="hidden text-sm font-semibold text-[#8B2014] underline-offset-2 hover:underline sm:block">More {{ $tour->category->name }}</a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $item)<x-tour-card :tour="$item" />@endforeach
            </div>
        </section>
    @endif
</div>

{{-- Mobile booking bar --}}
<div class="fixed inset-x-0 bottom-0 z-30 flex items-center justify-between gap-3 border-t border-stone-200 bg-white/95 px-4 py-3 shadow-[0_-8px_24px_rgba(0,0,0,0.08)] backdrop-blur lg:hidden">
    <x-price-badge :tour="$tour" />
    <a href="#book" class="{{ $btnClass }} px-7">Book Now</a>
</div>
@endsection