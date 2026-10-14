@props(['items', 'route', 'heading', 'subtitle' => null, 'nameKey' => 'name'])

@if ($items->isNotEmpty())
    @php $sectionId = 'items-' . \Illuminate\Support\Str::slug($heading); @endphp

    <section class="mx-auto mt-16 max-w-7xl px-4 sm:mt-20" aria-labelledby="{{ $sectionId }}">
        {{-- Section header --}}
        <div class="mb-8 max-w-2xl">
            <span class="mb-3 block h-1 w-14 rounded-full bg-[#8B2014]" aria-hidden="true"></span>
            <h2 id="{{ $sectionId }}" class="text-2xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-3xl">
                {{ $heading }}
            </h2>
            @if ($subtitle)
                <p class="mt-2 text-base leading-relaxed text-stone-600">{{ $subtitle }}</p>
            @endif
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
            @foreach ($items as $item)
                <a href="{{ route($route, $item->slug) }}"
                   class="group flex flex-col overflow-hidden rounded-3xl bg-white shadow-md shadow-stone-900/5 ring-1 ring-stone-200 transition duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-stone-900/15 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#8B2014]/50 focus-visible:ring-offset-2">

                    {{-- Image --}}
                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-amber-100 via-amber-50 to-stone-100">
                        <x-cover :model="$item"
                                 class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110" />
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/35 via-transparent to-transparent opacity-70 transition-opacity duration-300 group-hover:opacity-100" aria-hidden="true"></div>
                    </div>

                    {{-- Content --}}
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-lg font-extrabold leading-snug text-stone-900 transition-colors group-hover:text-[#8B2014]">
                            {{ $item->{$nameKey} }}
                        </h3>
                        <span class="mt-2 block h-0.5 w-8 rounded-full bg-amber-400 transition-all duration-300 group-hover:w-14" aria-hidden="true"></span>

                        <p class="mt-3 text-sm leading-relaxed text-stone-600">
                            {{ \Illuminate\Support\Str::limit((string) $item->description, 90) }}
                        </p>

                        <span class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-bold text-[#8B2014]">
                            Learn more
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif