@props(['tours', 'heading', 'subtitle' => null, 'link' => null])

@if ($tours->isNotEmpty())
    @php $sectionId = 'tours-' . \Illuminate\Support\Str::slug($heading); @endphp

    <section class="mx-auto mt-16 max-w-7xl px-4 sm:mt-20" aria-labelledby="{{ $sectionId }}">
        {{-- Section header --}}
        <div class="mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-4">
            <div class="max-w-2xl">
                <span class="mb-3 block h-1 w-12 rounded-full bg-[#8B2014]" aria-hidden="true"></span>
                <h2 id="{{ $sectionId }}" class="text-2xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-3xl">
                    {{ $heading }}
                </h2>
                @if ($subtitle)
                    <p class="mt-2 text-base leading-relaxed text-stone-600">{{ $subtitle }}</p>
                @endif
            </div>

            @if ($link)
                <a href="{{ $link }}"
                   class="group inline-flex min-h-[44px] items-center gap-2 rounded-full border border-[#8B2014]/25 bg-white px-5 py-2 text-sm font-bold text-[#8B2014] transition hover:border-[#8B2014] hover:bg-[#8B2014] hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2">
                    View all
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            @endif
        </div>

        {{-- Tour cards --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
            @foreach ($tours as $tour)
                <x-tour-card :tour="$tour" />
            @endforeach
        </div>
    </section>
@endif