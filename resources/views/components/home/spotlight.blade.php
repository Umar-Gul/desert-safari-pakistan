@props(['tour'])

<section class="mx-auto mt-16 max-w-7xl px-4" aria-labelledby="featured-tour-title">
    <div x-data="{ tab: 'overview' }"
         class="group/card grid overflow-hidden rounded-3xl bg-white shadow-lg shadow-stone-900/5 ring-1 ring-stone-200 transition-shadow duration-300 hover:shadow-xl hover:shadow-stone-900/10 lg:grid-cols-2">

        {{-- Image side --}}
        <div class="relative min-h-[16rem] overflow-hidden bg-gradient-to-br from-amber-600 via-[#8B2014] to-stone-900 sm:min-h-[20rem]">
            <x-cover :model="$tour" conversion="large"
                     class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-out group-hover/card:scale-105" />
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/60 via-stone-950/5 to-transparent" aria-hidden="true"></div>

            <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-[#8B2014] shadow-sm backdrop-blur">
                <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Featured experience
            </span>
        </div>

        {{-- Content side --}}
        <div class="flex flex-col p-6 sm:p-8 lg:p-10">
            <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-[#8B2014]">
                <span class="h-0.5 w-6 bg-amber-400" aria-hidden="true"></span>
                Featured experience
            </p>
            <h2 id="featured-tour-title" class="mt-2 text-2xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-3xl">
                {{ $tour->title }}
            </h2>

            {{-- Tabs --}}
            <div role="tablist" aria-label="Tour details"
                 class="mt-5 inline-flex w-full gap-1 rounded-xl bg-stone-100 p-1 text-sm sm:w-auto">
                @foreach (['overview' => 'Overview', 'highlights' => 'Highlights', 'itinerary' => 'Plan'] as $key => $label)
                    <button type="button"
                            role="tab"
                            id="featured-tab-{{ $key }}"
                            aria-controls="featured-panel-{{ $key }}"
                            x-on:click="tab = '{{ $key }}'"
                            :aria-selected="(tab === '{{ $key }}').toString()"
                            :tabindex="tab === '{{ $key }}' ? 0 : -1"
                            :class="tab === '{{ $key }}' ? 'bg-white text-[#8B2014] shadow-sm' : 'text-stone-600 hover:text-stone-900'"
                            class="flex-1 rounded-lg px-4 py-2 font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2 sm:flex-none">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Panels --}}
            <div class="mt-5 min-h-[9rem] text-sm leading-relaxed text-stone-700 sm:text-[15px]">
                <div id="featured-panel-overview" role="tabpanel" aria-labelledby="featured-tab-overview"
                     x-show="tab === 'overview'"
                     x-transition:enter="transition duration-300 ease-out"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <p>{{ \Illuminate\Support\Str::limit((string) $tour->short_description, 260) }}</p>
                </div>

                <div id="featured-panel-highlights" role="tabpanel" aria-labelledby="featured-tab-highlights"
                     x-show="tab === 'highlights'" x-cloak
                     x-transition:enter="transition duration-300 ease-out"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <ul class="space-y-2.5">
                        @forelse ($tour->highlights->take(6) as $h)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#8B2014]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $h->text }}</span>
                            </li>
                        @empty
                            <li class="text-stone-500">Details on the tour page.</li>
                        @endforelse
                    </ul>
                </div>

                <div id="featured-panel-itinerary" role="tabpanel" aria-labelledby="featured-tab-itinerary"
                     x-show="tab === 'itinerary'" x-cloak
                     x-transition:enter="transition duration-300 ease-out"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <ul class="space-y-3">
                        @forelse ($tour->itineraryItems->take(5) as $stop)
                            <li class="relative flex items-start gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-[#8B2014] ring-4 ring-[#8B2014]/10" aria-hidden="true"></span>
                                <span>
                                    <span class="font-bold text-stone-900">{{ $stop->time_label }}</span>
                                    <span class="text-stone-700">{{ $stop->title }}</span>
                                </span>
                            </li>
                        @empty
                            @foreach ($tour->inclusions->take(5) as $item)
                                <li class="flex items-start gap-2.5">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $item->text }}</span>
                                </li>
                            @endforeach
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Price and call to action --}}
            <div class="mt-auto flex flex-wrap items-center justify-between gap-4 border-t border-stone-200 pt-6">
                <x-price-badge :tour="$tour" size="lg" />
                <a href="{{ route('tours.show', $tour->slug) }}"
                   class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-full bg-[#8B2014] px-7 py-3 text-sm font-bold text-white shadow-md shadow-[#8B2014]/25 transition hover:-translate-y-0.5 hover:bg-[#6f190f] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2 max-sm:w-full">
                    View &amp; book
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>