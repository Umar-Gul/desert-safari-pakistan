@props(['packages'])

@if ($packages->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-extrabold tracking-tight text-stone-900 sm:text-3xl">School trips, corporate &amp; private events</h2>
                <p class="mt-1.5 text-stone-600">Group packages with private camps, food and entertainment.</p>
            </div>
            <a href="{{ route('events.index') }}" class="text-sm font-semibold text-[#8B2014] underline-offset-2 hover:underline">All packages &rarr;</a>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($packages as $package)
                <article class="group relative isolate flex min-h-[30rem] flex-col justify-end overflow-hidden rounded-3xl bg-[#8B2014] shadow-md ring-1 ring-stone-200 transition hover:-translate-y-1 hover:shadow-xl">

                    {{-- Background image --}}
                    <x-cover :model="$package" class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 group-hover:scale-105" />

                    {{-- Readability overlay --}}
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-stone-950/95 via-stone-900/70 to-stone-900/20" aria-hidden="true"></div>

                    @if ($package->time_slot)
                        <p class="absolute left-5 top-5 inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-[#8B2014] shadow backdrop-blur">{{ $package->time_slot }}</p>
                    @endif

                    <div class="p-5 pt-24 text-white sm:p-6 sm:pt-24">
                        <h3 class="text-2xl font-extrabold leading-snug drop-shadow">{{ $package->title }}</h3>

                        <ul class="mt-4 space-y-2 text-sm text-white/90">
                            @foreach (array_slice((array) $package->features, 0, 4) as $feature)
                                <li class="flex gap-2.5">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-5 flex items-center justify-between gap-3 rounded-2xl bg-white/15 p-3 ring-1 ring-white/20 backdrop-blur">
                            <p class="text-sm text-white/80">from <span class="text-xl font-extrabold text-white">{{ money($package->adult_price) }}</span></p>
                            <a href="{{ route('contact', ['subject' => 'Enquiry: '.$package->title]) }}"
                               class="inline-flex min-h-[44px] items-center justify-center rounded-xl bg-white px-5 text-sm font-bold text-[#8B2014] shadow-sm transition hover:bg-[#fbf3ea] focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Enquire</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif