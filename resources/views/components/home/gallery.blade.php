@props(['items'])

@if ($items->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-2xl font-extrabold text-stone-900 sm:text-3xl">Photo moments</h2>
            <a href="{{ route('gallery.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">Full gallery &rarr;</a>
        </div>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ($items as $item)
                <a href="{{ route('gallery.index') }}" class="group relative block aspect-square overflow-hidden rounded-xl bg-stone-200">
                    <x-cover :model="$item" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                </a>
            @endforeach
        </div>
    </section>
@endif