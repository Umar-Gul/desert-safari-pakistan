@props(['items', 'route', 'heading', 'subtitle' => null, 'nameKey' => 'name'])

@if ($items->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-stone-900 sm:text-3xl">{{ $heading }}</h2>
            @if ($subtitle)<p class="mt-1 text-stone-600">{{ $subtitle }}</p>@endif
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($items as $item)
                <a href="{{ route($route, $item->slug) }}" class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-stone-200 transition hover:shadow-md">
                    <div class="aspect-[4/3] overflow-hidden bg-stone-100">
                        <x-cover :model="$item" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-stone-900">{{ $item->{$nameKey} }}</h3>
                        <p class="mt-1 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit((string) $item->description, 90) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif