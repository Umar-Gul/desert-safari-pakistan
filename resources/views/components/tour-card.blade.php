@props(['tour'])

<article class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition hover:shadow-md">
    <a href="{{ route('tours.show', $tour->slug) }}" class="relative block aspect-[4/3] overflow-hidden bg-stone-100">
        <x-cover :model="$tour" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />

        <div class="absolute left-3 top-3 flex flex-wrap gap-1">
            @foreach ($tour->badges ?? [] as $badge)
                <span class="rounded-full bg-amber-600 px-2.5 py-0.5 text-xs font-semibold text-white">{{ $badge->label() }}</span>
            @endforeach
        </div>

        @if ($tour->has_discount)
            <span class="absolute right-3 top-3 rounded-full bg-red-600 px-2.5 py-0.5 text-xs font-bold text-white">-{{ $tour->discount_percent }}%</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-amber-700">{{ $tour->desert?->name ?? $tour->category?->name }}</p>
        <h3 class="mt-1 text-lg font-bold leading-snug text-stone-900">
            <a href="{{ route('tours.show', $tour->slug) }}" class="hover:text-amber-700">{{ $tour->title }}</a>
        </h3>
        <p class="mt-1 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit((string) $tour->short_description, 110) }}</p>

        <ul class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs text-stone-600">
            <li>{{ rtrim(rtrim(number_format((float) $tour->duration_hours, 1), '0'), '.') }} hrs</li>
            @if ($tour->has_dinner)<li>Dinner</li>@endif
            @if ($tour->has_breakfast)<li>Breakfast</li>@endif
            @if ($tour->has_guide)<li>Guide</li>@endif
            @if ($tour->has_transfer)<li>Transfer</li>@endif
        </ul>

        <div class="mt-auto flex items-end justify-between gap-3 pt-4">
            <div>
                <x-rating :value="$tour->rating_cached" :count="$tour->reviews_count_cached" />
                <x-price-badge :tour="$tour" class="mt-1" />
            </div>
            <a href="{{ route('tours.show', $tour->slug) }}" class="btn btn-primary whitespace-nowrap">Book Now</a>
        </div>
    </div>
</article>