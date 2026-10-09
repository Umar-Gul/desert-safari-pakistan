@extends('layouts.frontend')

@section('title', 'School Trips, Corporate & Private Events')
@section('meta_description', 'Group packages for school trips, corporate events, weddings, product launches and conferences in the desert.')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <x-breadcrumbs :items="[['Events', null]]" />
    <header class="mb-8 mt-3 max-w-3xl">
        <h1 class="text-3xl font-extrabold text-stone-900">School trips, corporate &amp; private events</h1>
        <p class="mt-2 text-stone-600">Tell us your group size and date and we will build a package around it.</p>
    </header>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($packages as $package)
            <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200">
                <div class="aspect-[16/9] bg-stone-100"><x-cover :model="$package" class="h-full w-full object-cover" /></div>
                <div class="flex flex-1 flex-col p-5">
                    <h2 class="text-xl font-bold text-stone-900">{{ $package->title }}</h2>
                    @if ($package->time_slot)<p class="text-sm text-stone-500">{{ $package->time_slot }}</p>@endif
                    @if ($package->description)<p class="mt-2 text-sm text-stone-600">{{ $package->description }}</p>@endif
                    <ul class="mt-3 space-y-1 text-sm text-stone-700">
                        @foreach ((array) $package->features as $feature)<li>&#10003; {{ $feature }}</li>@endforeach
                    </ul>
                    <div class="mt-auto pt-4">
                        <p class="text-sm text-stone-600">Adults {{ money($package->adult_price) }} &middot; Children {{ money($package->child_price) }}</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('contact', ['subject' => 'Enquiry: '.$package->title]) }}" class="btn btn-primary">Enquire</a>
                            <a href="{{ whatsapp_url('Hello, I would like a quote for: '.$package->title) }}" target="_blank" rel="noopener" class="btn btn-whatsapp">WhatsApp</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <p class="text-stone-600 md:col-span-3">Event packages are coming soon. <a href="{{ route('contact') }}" class="text-amber-700 underline">Contact us</a> for a custom quote.</p>
        @endforelse
    </div>
</div>
@endsection