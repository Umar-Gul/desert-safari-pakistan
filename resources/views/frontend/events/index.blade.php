@extends('layouts.frontend')

@section('title', 'School Trips, Corporate & Private Events')
@section('meta_description', 'Group packages for school trips, corporate events, weddings, product launches and conferences in the desert.')

@section('content')
{{-- Hero --}}
<section class="border-b border-stone-200 bg-gradient-to-b from-[#fbf3ea] to-white">
    <div class="mx-auto max-w-7xl px-4 pb-10 pt-6 sm:pb-12">
        <x-breadcrumbs :items="[['Events', null]]" />

        <div class="mt-6 max-w-3xl">
            <span class="inline-flex items-center rounded-full bg-[#8B2014]/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#8B2014]">Group packages</span>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl lg:text-5xl">School trips, corporate &amp; private events</h1>
            <p class="mt-3 text-base leading-relaxed text-stone-600 sm:text-lg">Tell us your group size and date and we will build a package around it.</p>

            <ul class="mt-5 flex flex-wrap gap-2 text-sm">
                @foreach (['School trips', 'Corporate events', 'Weddings', 'Product launches', 'Conferences'] as $type)
                    <li class="inline-flex items-center rounded-full bg-white px-3.5 py-1.5 font-medium text-stone-700 ring-1 ring-stone-200">{{ $type }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- Packages --}}
<div class="mx-auto max-w-7xl px-4 py-10 sm:py-14">
    @if ($packages->isEmpty())
        <div class="rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-stone-200">
            <h2 class="text-lg font-bold text-stone-900">Event packages are coming soon</h2>
            <p class="mx-auto mt-1 max-w-md text-sm text-stone-600">Tell us what you have in mind and we will prepare a custom quote.</p>
            <a href="{{ route('contact') }}" class="mt-6 inline-flex min-h-[46px] items-center justify-center rounded-xl bg-[#8B2014] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#741a10]">Contact us for a custom quote</a>
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($packages as $package)
                <article class="group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200 transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="aspect-[16/9] overflow-hidden bg-stone-100">
                        <x-cover :model="$package" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                    </div>

                    <div class="flex flex-1 flex-col p-5 sm:p-6">
                        @if ($package->time_slot)
                            <p class="inline-flex w-fit items-center gap-1.5 rounded-full bg-[#8B2014]/10 px-2.5 py-1 text-xs font-semibold text-[#8B2014]">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.29.71l2.5 2.5a1 1 0 001.42-1.42L11 9.59V6z" clip-rule="evenodd"/></svg>
                                {{ $package->time_slot }}
                            </p>
                        @endif

                        <h2 class="mt-3 text-xl font-bold leading-snug text-stone-900">{{ $package->title }}</h2>

                        @if ($package->description)
                            <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $package->description }}</p>
                        @endif

                        @if (count((array) $package->features))
                            <ul class="mt-4 space-y-2 text-sm text-stone-700">
                                @foreach ((array) $package->features as $feature)
                                    <li class="flex gap-2.5">
                                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-auto pt-6">
                            <div class="grid grid-cols-2 divide-x divide-stone-200 rounded-2xl bg-[#fbf3ea] py-3 text-center">
                                <div class="px-2">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Adults</p>
                                    <p class="mt-0.5 text-lg font-extrabold text-[#8B2014]">{{ money($package->adult_price) }}</p>
                                </div>
                                <div class="px-2">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Children</p>
                                    <p class="mt-0.5 text-lg font-extrabold text-[#8B2014]">{{ money($package->child_price) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                <a href="{{ route('contact', ['subject' => 'Enquiry: '.$package->title]) }}"
                                   class="inline-flex min-h-[46px] flex-1 items-center justify-center rounded-xl bg-[#8B2014] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#741a10] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2">Enquire</a>
                                <a href="{{ whatsapp_url('Hello, I would like a quote for: '.$package->title) }}" target="_blank" rel="noopener"
                                   class="inline-flex min-h-[46px] flex-1 items-center justify-center rounded-xl border border-[#25D366] bg-white px-4 text-sm font-semibold text-[#128C7E] transition hover:bg-[#25D366]/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2">WhatsApp</a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

{{-- Custom quote band --}}
<section class="px-4 pb-14">
    <div class="mx-auto max-w-7xl rounded-3xl bg-[#8B2014] px-6 py-10 text-center shadow-lg sm:px-12">
        <h2 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Need something different?</h2>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">Share your group size, date and ideas and we will put together a custom package.</p>
        <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ route('contact', ['subject' => 'Custom event quote']) }}"
               class="inline-flex min-h-[48px] w-full items-center justify-center rounded-xl bg-white px-6 text-sm font-bold text-[#8B2014] shadow-sm transition hover:bg-[#fbf3ea] sm:w-auto">Request a custom quote</a>
            <a href="{{ whatsapp_url('Hello, I would like a custom quote for an event.') }}" target="_blank" rel="noopener"
               class="inline-flex min-h-[48px] w-full items-center justify-center rounded-xl border border-white/40 px-6 text-sm font-bold text-white transition hover:bg-white/10 sm:w-auto">Ask on WhatsApp</a>
        </div>
    </div>
</section>
@endsection