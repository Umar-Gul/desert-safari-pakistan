@props(['tour'])

<section class="mx-auto mt-16 max-w-7xl px-4">
    <div x-data="{ tab: 'overview' }" class="grid overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200 lg:grid-cols-2">
        <div class="relative min-h-[16rem]">
            <x-cover :model="$tour" conversion="large" class="absolute inset-0 h-full w-full object-cover" />
        </div>
        <div class="p-6 sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Featured experience</p>
            <h2 class="mt-1 text-2xl font-extrabold text-stone-900">{{ $tour->title }}</h2>

            <div class="mt-4 flex gap-1 border-b border-stone-200 text-sm">
                @foreach (['overview' => 'Overview', 'highlights' => 'Highlights', 'itinerary' => 'Plan'] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-amber-600 text-amber-700' : 'border-transparent text-stone-600'"
                            class="-mb-px border-b-2 px-3 py-2 font-medium">{{ $label }}</button>
                @endforeach
            </div>

            <div class="mt-4 min-h-[8rem] text-sm text-stone-700">
                <p x-show="tab === 'overview'">{{ \Illuminate\Support\Str::limit((string) $tour->short_description, 260) }}</p>

                <ul x-show="tab === 'highlights'" x-cloak class="list-disc space-y-1 pl-5">
                    @forelse ($tour->highlights->take(6) as $h)<li>{{ $h->text }}</li>@empty<li>Details on the tour page.</li>@endforelse
                </ul>

                <ul x-show="tab === 'itinerary'" x-cloak class="space-y-1">
                    @forelse ($tour->itineraryItems->take(5) as $stop)
                        <li><span class="font-semibold text-stone-900">{{ $stop->time_label }}</span> {{ $stop->title }}</li>
                    @empty
                        @foreach ($tour->inclusions->take(5) as $item)<li>&#10003; {{ $item->text }}</li>@endforeach
                    @endforelse
                </ul>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <x-price-badge :tour="$tour" size="lg" />
                <a href="{{ route('tours.show', $tour->slug) }}" class="btn btn-primary">View &amp; book</a>
            </div>
        </div>
    </div>
</section>