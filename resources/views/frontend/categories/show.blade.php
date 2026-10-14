@extends('layouts.frontend')

@section('title', $category->meta_title ?: $category->name)
@section('meta_description', $category->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $category->description), 155))
@section('canonical', $category->canonical ?: route('categories.show', $category->slug))

@section('content')
{{-- Header strip (same style as the tour and checkout pages) --}}
<section class="relative overflow-hidden border-b border-stone-200 bg-gradient-to-b from-[#fbf3ea] to-white">
    <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#8B2014]/10 blur-3xl" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-4 pb-8 pt-6 sm:pb-10">
        <x-breadcrumbs :items="[['All activities', route('tours.index')], [$category->name, null]]" />

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-[#8B2014]/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#8B2014]">Activity category</span>
            @isset($tours)
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-stone-700 ring-1 ring-stone-200">
                    {{ number_format($tours->total()) }} {{ \Illuminate\Support\Str::plural('tour', $tours->total()) }}
                </span>
            @endisset
        </div>

        <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl lg:text-5xl">{{ $category->name }}</h1>

        @if ($category->description)
            <p class="mt-3 max-w-3xl text-base leading-relaxed text-stone-600 sm:text-lg">{{ $category->description }}</p>
        @endif

        <ul class="mt-5 flex flex-wrap gap-2 text-sm">
            @foreach (['Free cancellation on most tours', 'Local, experienced guides', 'No payment until confirmed'] as $point)
                <li class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3.5 py-1.5 font-medium text-emerald-800 ring-1 ring-emerald-100">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    {{ $point }}
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Listing --}}
<div class="mx-auto max-w-7xl px-4 py-8 sm:py-12">
    @include('frontend.partials.tour-list', ['action' => route('categories.show', $category->slug), 'hideCategory' => true])
</div>

{{-- Closing help band --}}
<section class="px-4 pb-14">
    <div class="mx-auto max-w-7xl overflow-hidden rounded-3xl bg-[#8B2014] px-6 py-10 text-center shadow-lg sm:px-12">
        <h2 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Can't find the right {{ \Illuminate\Support\Str::lower($category->name) }} tour?</h2>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">Tell us your dates and group size and we will plan something for you.</p>
        <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ route('contact') }}"
               class="inline-flex min-h-[48px] w-full items-center justify-center rounded-xl bg-white px-6 text-sm font-bold text-[#8B2014] shadow-sm transition hover:bg-[#fbf3ea] focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#8B2014] sm:w-auto">Contact us</a>
            <a href="{{ whatsapp_url('Hello, I need help choosing a tour: '.$category->name) }}" target="_blank" rel="noopener"
               class="inline-flex min-h-[48px] w-full items-center justify-center rounded-xl border border-white/40 px-6 text-sm font-bold text-white transition hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:w-auto">Ask on WhatsApp</a>
        </div>
    </div>
</section>
@endsection