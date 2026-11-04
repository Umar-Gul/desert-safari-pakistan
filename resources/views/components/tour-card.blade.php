@props(['tour'])

<article class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition duration-300 focus-within:ring-2 focus-within:ring-amber-500 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-stone-900/10 motion-reduce:transition-none motion-reduce:hover:translate-y-0">
    <a href="{{ route('tours.show', $tour->slug) }}" tabindex="-1" aria-hidden="true" class="relative block aspect-[4/3] overflow-hidden bg-stone-100">
        <x-cover :model="$tour" class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100" />
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-stone-900/35 via-transparent to-transparent"></div>

        <div class="absolute left-3 top-3 flex max-w-[70%] flex-wrap gap-1.5">
            @foreach ($tour->badges ?? [] as $badge)
                <span class="rounded-full bg-amber-600 px-2.5 py-1 text-xs font-semibold leading-none text-white shadow-sm">{{ $badge->label() }}</span>
            @endforeach
        </div>

        @if ($tour->has_discount)
            <span class="absolute right-3 top-3 rounded-full bg-red-600 px-2.5 py-1 text-xs font-bold leading-none text-white shadow-sm">-{{ $tour->discount_percent }}%</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        @if ($tour->availability_status ?? false)
            @php
                $availabilityStatus = $tour->availability_status['status'] ?? 'closed';
                $availabilityLabel = match ($availabilityStatus) {
                    'available' => $tour->availability_status['remaining'].' spots left',
                    'full' => 'Fully Booked',
                    default => 'Closed',
                };
                $availabilityClass = match ($availabilityStatus) {
                    'available' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
                    'full' => 'bg-amber-50 text-amber-800 ring-amber-200',
                    default => 'bg-red-50 text-red-800 ring-red-200',
                };
                $availabilityDot = match ($availabilityStatus) {
                    'available' => 'bg-emerald-500',
                    'full' => 'bg-amber-500',
                    default => 'bg-red-500',
                };
            @endphp
            <p class="mb-2.5 inline-flex w-fit items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $availabilityClass }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $availabilityDot }}" aria-hidden="true"></span>{{ $availabilityLabel }}
            </p>
        @endif

        <p class="text-xs font-semibold text-amber-700">{{ $tour->desert?->name ?? $tour->category?->name }}</p>

        <h3 class="mt-1 line-clamp-2 text-lg font-bold leading-snug text-stone-900">
            {{-- Stretched link: the whole card is clickable --}}
            <a href="{{ route('tours.show', $tour->slug) }}" class="transition-colors after:absolute after:inset-0 after:content-[''] group-hover:text-amber-700 focus:outline-none">{{ $tour->title }}</a>
        </h3>

        <p class="mt-1.5 line-clamp-3 text-sm leading-relaxed text-stone-600">{{ \Illuminate\Support\Str::limit((string) $tour->short_description, 110) }}</p>

        <ul class="mt-3.5 flex flex-wrap gap-1.5 text-xs font-medium text-stone-700">
            <li class="rounded-md bg-stone-100 px-2 py-1">{{ rtrim(rtrim(number_format((float) $tour->duration_hours, 1), '0'), '.') }} hours</li>
            @if ($tour->has_dinner)<li class="rounded-md bg-stone-100 px-2 py-1">Dinner</li>@endif
            @if ($tour->has_breakfast)<li class="rounded-md bg-stone-100 px-2 py-1">Breakfast</li>@endif
            @if ($tour->has_guide)<li class="rounded-md bg-stone-100 px-2 py-1">Tour guide</li>@endif
            @if ($tour->has_transfer)<li class="rounded-md bg-stone-100 px-2 py-1">Pick &amp; drop</li>@endif
        </ul>

        <div class="mt-auto flex items-end justify-between gap-3 border-t border-stone-100 pt-4">
            <div class="min-w-0">
                <x-rating :value="$tour->rating_cached" :count="$tour->reviews_count_cached" />
                <x-price-badge :tour="$tour" class="mt-1" />
            </div>
            <a href="{{ route('tours.show', $tour->slug) }}" class="btn btn-primary relative z-10 whitespace-nowrap focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Book Now</a>
        </div>
    </div>
</article>