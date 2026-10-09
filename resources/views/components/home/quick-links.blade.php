@props(['links', 'heading'])

@if ($links->isNotEmpty())
    <section class="mx-auto mt-10 max-w-7xl px-4">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-stone-500">{{ $heading }}</h2>
        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach ($links as $link)
                <a href="{{ $link->url }}" class="whitespace-nowrap rounded-full border border-stone-300 bg-white px-4 py-1.5 text-sm text-stone-700 hover:border-amber-500 hover:text-amber-700">{{ $link->label }}</a>
            @endforeach
        </div>
    </section>
@endif