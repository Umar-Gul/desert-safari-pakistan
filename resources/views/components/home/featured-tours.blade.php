@props(['tours', 'heading', 'subtitle' => null, 'link' => null])

@if ($tours->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold text-stone-900 sm:text-3xl">{{ $heading }}</h2>
                @if ($subtitle)<p class="mt-1 text-stone-600">{{ $subtitle }}</p>@endif
            </div>
            @if ($link)<a href="{{ $link }}" class="text-sm font-semibold text-amber-700 hover:underline">View all &rarr;</a>@endif
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($tours as $tour)
                <x-tour-card :tour="$tour" />
            @endforeach
        </div>
    </section>
@endif