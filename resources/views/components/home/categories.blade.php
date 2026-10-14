@props(['categories'])

@if ($categories->isNotEmpty())
    <section class="mx-auto mt-16 max-w-7xl px-4 sm:mt-20" aria-labelledby="browse-by-activity">
        {{-- Section header --}}
        <div class="mb-10 max-w-2xl">
            <span class="mb-3 block h-1 w-14 rounded-full bg-[#8B2014]" aria-hidden="true"></span>
            <h2 id="browse-by-activity" class="text-3xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-4xl">
                Tour Categories
            </h2>
        </div>

        {{-- Category cards: 2 per row on phones and tablets, 4 per row on desktop --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4 lg:gap-6">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}"
                   aria-label="{{ $category->name }}, {{ $category->activities_count }} {{ \Illuminate\Support\Str::plural('activity', $category->activities_count) }}"
                   class="group relative block aspect-[4/5] overflow-hidden rounded-2xl bg-gradient-to-br from-amber-600 via-[#8B2014] to-stone-900 shadow-md ring-1 ring-stone-200 transition duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-stone-900/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#8B2014]/60 focus-visible:ring-offset-2">

                    <x-cover :model="$category"
                             class="absolute inset-0 h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110" />

                    {{-- Readability gradient --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/90 via-stone-950/20 to-transparent transition-opacity duration-300 group-hover:from-[#8B2014]/95" aria-hidden="true"></div>

                    {{-- Activity count --}}
                    <span class="absolute left-3 top-3 inline-flex items-center rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-[#8B2014] shadow-md backdrop-blur sm:left-5 sm:top-5 sm:text-sm">
                        {{ $category->activities_count }}
                        {{ \Illuminate\Support\Str::plural('Activity', $category->activities_count) }}
                    </span>

                    {{-- Arrow button, visible on hover and keyboard focus --}}
                    <span class="absolute right-3 top-3 hidden h-11 w-11 translate-y-1 items-center justify-center rounded-full bg-amber-400 text-stone-900 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 group-focus-visible:translate-y-0 group-focus-visible:opacity-100 sm:right-5 sm:top-5 sm:flex" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>

                    {{-- Name and call to action --}}
                    <div class="absolute inset-x-0 bottom-0 p-4 text-white sm:p-6">
                        <p class="text-lg font-extrabold leading-tight drop-shadow sm:text-2xl">{{ $category->name }}</p>
                        <span class="mt-3 block h-1 w-10 rounded-full bg-amber-400 transition-all duration-300 group-hover:w-20" aria-hidden="true"></span>
                        <span class="mt-3 hidden items-center gap-2 text-sm font-bold text-amber-200 sm:inline-flex">
                            Explore
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif