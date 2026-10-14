@php
    $hideCategory = $hideCategory ?? false;
    $sorts = ['popular' => 'Most popular', 'rating' => 'Top rated', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'newest' => 'Newest'];
    $durations = ['' => 'Any duration', 'short' => 'Up to 3 hours', 'half' => '3 to 6 hours', 'full' => '6 to 12 hours', 'overnight' => 'Overnight'];

    $val = fn (string $key) => (string) ($selected[$key] ?? '');

    // Active-filter chips (sort is not counted as a filter)
    $chips = [];
    if ($val('q') !== '') {
        $chips[] = ['keys' => ['q'], 'label' => '"' . $val('q') . '"'];
    }
    if (! $hideCategory && $val('category') !== '') {
        $name = collect($filters['categories'])->firstWhere('slug', $val('category'))->name ?? $val('category');
        $chips[] = ['keys' => ['category'], 'label' => $name];
    }
    if ($val('desert') !== '') {
        $name = collect($filters['deserts'])->firstWhere('slug', $val('desert'))->name ?? $val('desert');
        $chips[] = ['keys' => ['desert'], 'label' => $name];
    }
    if ($val('duration') !== '' && isset($durations[$val('duration')])) {
        $chips[] = ['keys' => ['duration'], 'label' => $durations[$val('duration')]];
    }
    if ($val('min_price') !== '') {
        $chips[] = ['keys' => ['min_price'], 'label' => 'From ' . number_format((float) $val('min_price'))];
    }
    if ($val('max_price') !== '') {
        $chips[] = ['keys' => ['max_price'], 'label' => 'Up to ' . number_format((float) $val('max_price'))];
    }
    if ($val('rating') !== '') {
        $chips[] = ['keys' => ['rating'], 'label' => $val('rating') . '+ stars'];
    }

    $activeCount = count($chips);
    $extraActive = $activeCount - ($val('q') !== '' ? 1 : 0);

    $removeUrl = function (array $keys) use ($action) {
        $query = array_filter(
            request()->except(array_merge($keys, ['page'])),
            fn ($v) => $v !== null && $v !== ''
        );

        return $action . ($query ? '?' . http_build_query($query) : '');
    };

    $inputClass = 'block w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 placeholder-stone-400 shadow-sm transition focus:border-[#8B2014] focus:outline-none focus:ring-2 focus:ring-[#8B2014]/20';
    $labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-500';
@endphp

{{-- Filter bar --}}
<form id="tour-filters" method="GET" action="{{ $action }}"
      class="mb-5 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-stone-200 sm:p-5">

    {{-- Row 1: search + actions --}}
    <div class="flex items-stretch gap-2 sm:gap-3">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-stone-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.48l4.31 4.32a1 1 0 01-1.4 1.4l-4.32-4.31A6 6 0 012 8z" clip-rule="evenodd"/></svg>
            <label for="f-q" class="sr-only">Search tours</label>
            <input id="f-q" type="search" name="q" value="{{ $val('q') }}" placeholder="Search tours, e.g. jeep safari, camping"
                   class="{{ $inputClass }} min-h-[48px] pl-11">
        </div>

        <button type="button" id="filter-toggle"
                class="inline-flex min-h-[48px] items-center gap-2 rounded-xl border border-stone-300 bg-white px-4 text-sm font-semibold text-stone-800 shadow-sm transition hover:bg-stone-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] lg:hidden"
                aria-controls="filter-extra" aria-expanded="{{ $extraActive ? 'true' : 'false' }}">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 5a1 1 0 011-1h12a1 1 0 010 2H4a1 1 0 01-1-1zm3 5a1 1 0 011-1h8a1 1 0 010 2H7a1 1 0 01-1-1zm3 5a1 1 0 011-1h2a1 1 0 010 2h-2a1 1 0 01-1-1z"/></svg>
            <span class="hidden sm:inline">Filters</span>
            @if ($extraActive > 0)
                <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[#8B2014] px-1.5 text-xs font-bold text-white">{{ $extraActive }}</span>
            @endif
        </button>

        <button type="submit"
                class="inline-flex min-h-[48px] items-center justify-center rounded-xl bg-[#8B2014] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#741a10] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014] focus-visible:ring-offset-2 sm:px-7">
            Search
        </button>
    </div>

    {{-- Row 2: filters (collapsible on mobile, always visible on desktop) --}}
    <div id="filter-extra"
         class="{{ $extraActive ? 'grid' : 'hidden' }} mt-4 gap-4 border-t border-stone-100 pt-4 sm:grid-cols-2 lg:grid lg:grid-cols-12 lg:items-end">

        @unless ($hideCategory)
            <div class="lg:col-span-2">
                <label for="f-category" class="{{ $labelClass }}">Category</label>
                <select id="f-category" name="category" class="{{ $inputClass }}">
                    <option value="">All categories</option>
                    @foreach ($filters['categories'] as $c)
                        <option value="{{ $c->slug }}" @selected($val('category') === $c->slug)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        @endunless

        <div class="{{ $hideCategory ? 'lg:col-span-3' : 'lg:col-span-2' }}">
            <label for="f-desert" class="{{ $labelClass }}">Desert</label>
            <select id="f-desert" name="desert" class="{{ $inputClass }}">
                <option value="">All deserts</option>
                @foreach ($filters['deserts'] as $d)
                    <option value="{{ $d->slug }}" @selected($val('desert') === $d->slug)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="{{ $hideCategory ? 'lg:col-span-3' : 'lg:col-span-2' }}">
            <label for="f-duration" class="{{ $labelClass }}">Duration</label>
            <select id="f-duration" name="duration" class="{{ $inputClass }}">
                @foreach ($durations as $value => $label)
                    <option value="{{ $value }}" @selected($val('duration') === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2 lg:col-span-3">
            <span class="{{ $labelClass }}">Price</span>
            <div class="flex items-center gap-2">
                <input type="number" min="0" name="min_price" value="{{ $val('min_price') }}" placeholder="Min" class="{{ $inputClass }}" aria-label="Minimum price">
                <span class="text-xs text-stone-400" aria-hidden="true">to</span>
                <input type="number" min="0" name="max_price" value="{{ $val('max_price') }}" placeholder="Max" class="{{ $inputClass }}" aria-label="Maximum price">
            </div>
        </div>

        <div class="lg:col-span-2">
            <label for="f-rating" class="{{ $labelClass }}">Rating</label>
            <select id="f-rating" name="rating" class="{{ $inputClass }}">
                <option value="">Any rating</option>
                @foreach ([5, 4, 3] as $r)
                    <option value="{{ $r }}" @selected($val('rating') === (string) $r)>{{ $r }}+ stars</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center sm:col-span-2 lg:col-span-1 lg:justify-end">
            <a href="{{ $action }}" class="inline-flex min-h-[44px] items-center text-sm font-semibold text-stone-600 underline-offset-2 transition hover:text-stone-900 hover:underline">Reset</a>
        </div>
    </div>
</form>

{{-- Results bar: count + sort --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-stone-600">
        <span class="font-semibold text-stone-900">{{ number_format($tours->total()) }}</span>
        {{ \Illuminate\Support\Str::plural('tour', $tours->total()) }} found
    </p>

    <div class="flex items-center gap-2">
        <label for="sort" class="text-sm text-stone-500">Sort by</label>
        <select id="sort" name="sort" form="tour-filters" class="{{ $inputClass }} !w-auto !py-2 pr-9">
            @foreach ($sorts as $value => $label)
                <option value="{{ $value }}" @selected($val('sort') === $value || ($val('sort') === '' && $loop->first))>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Active filter chips --}}
@if ($activeCount)
    <div class="mb-6 flex flex-wrap items-center gap-2" aria-label="Active filters">
        @foreach ($chips as $chip)
            <a href="{{ $removeUrl($chip['keys']) }}"
               class="group inline-flex items-center gap-1.5 rounded-full bg-[#8B2014]/10 py-1.5 pl-3 pr-2 text-sm font-medium text-[#8B2014] transition hover:bg-[#8B2014]/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8B2014]"
               title="Remove filter">
                {{ $chip['label'] }}
                <svg class="h-4 w-4 opacity-70 group-hover:opacity-100" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 5.3a1 1 0 011.4 0L10 7.6l2.3-2.3a1 1 0 111.4 1.4L11.4 9l2.3 2.3a1 1 0 01-1.4 1.4L10 10.4l-2.3 2.3a1 1 0 01-1.4-1.4L8.6 9 6.3 6.7a1 1 0 010-1.4z"/></svg>
                <span class="sr-only">Remove filter</span>
            </a>
        @endforeach
        <a href="{{ $action }}" class="ml-1 text-sm font-semibold text-stone-600 underline-offset-2 hover:text-stone-900 hover:underline">Clear all</a>
    </div>
@endif

{{-- Results --}}
@if ($tours->isEmpty())
    <div class="rounded-2xl bg-white px-6 py-14 text-center shadow-sm ring-1 ring-stone-200">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#8B2014]/10 text-[#8B2014]">
            <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.48l4.31 4.32a1 1 0 01-1.4 1.4l-4.32-4.31A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        </div>
        <h3 class="mt-4 text-lg font-bold text-stone-900">No tours match your filters</h3>
        <p class="mx-auto mt-1 max-w-md text-sm text-stone-600">Try removing a filter or widening your price range, or <a href="{{ route('contact') }}" class="font-semibold text-[#8B2014] underline underline-offset-2">contact us</a> and we will plan something for you.</p>
        @if ($activeCount)
            <a href="{{ $action }}" class="mt-6 inline-flex min-h-[46px] items-center justify-center rounded-xl bg-[#8B2014] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#741a10]">Clear all filters</a>
        @endif
    </div>
@else
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($tours as $tour)
            <x-tour-card :tour="$tour" />
        @endforeach
    </div>
@endif

<div class="mt-10">{{ $tours->withQueryString()->links() }}</div>

<script>
    (function () {
        var form = document.getElementById('tour-filters');
        var toggle = document.getElementById('filter-toggle');
        var extra = document.getElementById('filter-extra');
        var sort = document.getElementById('sort');
        if (!form) return;

        // Mobile: show or hide the filter row
        if (toggle && extra) {
            toggle.addEventListener('click', function () {
                var open = extra.classList.contains('hidden');
                extra.classList.toggle('hidden', !open);
                extra.classList.toggle('grid', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        // Dropdowns and sort apply instantly. Typed fields use the Search button or Enter.
        if (sort) sort.addEventListener('change', function () { form.requestSubmit(); });
        form.addEventListener('change', function (e) {
            if (e.target.tagName === 'SELECT') form.requestSubmit();
        });
    })();
</script>