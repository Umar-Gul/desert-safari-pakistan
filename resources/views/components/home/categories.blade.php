@props(['categories'])

@if ($categories->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <h2 class="mb-6 text-2xl font-extrabold text-stone-900 sm:text-3xl">Browse by activity</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="group relative block aspect-[4/5] overflow-hidden rounded-2xl bg-stone-200">
                    <x-cover :model="$category" class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-3 text-white">
                        <p class="font-bold leading-tight">{{ $category->name }}</p>
                        <p class="text-xs text-amber-100">{{ $category->activities_count }} {{ \Illuminate\Support\Str::plural('activity', $category->activities_count) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif