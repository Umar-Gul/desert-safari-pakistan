@props(['packages'])

@if ($packages->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold text-stone-900 sm:text-3xl">School trips, corporate &amp; private events</h2>
                <p class="mt-1 text-stone-600">Group packages with private camps, food and entertainment.</p>
            </div>
            <a href="{{ route('events.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">All packages &rarr;</a>
        </div>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($packages as $package)
                <div class="flex flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
                    <h3 class="text-lg font-bold text-stone-900">{{ $package->title }}</h3>
                    @if ($package->time_slot)<p class="text-sm text-stone-500">{{ $package->time_slot }}</p>@endif
                    <ul class="mt-3 space-y-1 text-sm text-stone-700">
                        @foreach (array_slice((array) $package->features, 0, 4) as $feature)<li>&#10003; {{ $feature }}</li>@endforeach
                    </ul>
                    <div class="mt-auto flex items-end justify-between pt-4">
                        <p class="text-sm text-stone-600">from <span class="text-lg font-extrabold text-stone-900">{{ money($package->adult_price) }}</span></p>
                        <a href="{{ route('contact', ['subject' => 'Enquiry: '.$package->title]) }}" class="btn btn-outline">Enquire</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif