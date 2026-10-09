@php
    $hideCategory = $hideCategory ?? false;
    $sorts = ['popular' => 'Most popular', 'rating' => 'Top rated', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'newest' => 'Newest'];
    $durations = ['' => 'Any duration', 'short' => 'Up to 3 hours', 'half' => '3 to 6 hours', 'full' => '6 to 12 hours', 'overnight' => 'Overnight'];
@endphp

<form method="GET" action="{{ $action }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-stone-200 sm:grid-cols-2 lg:grid-cols-4">
    <input type="search" name="q" value="{{ $selected['q'] }}" placeholder="Search tours" class="field" aria-label="Search">

    @unless ($hideCategory)
        <select name="category" class="field" aria-label="Category">
            <option value="">All categories</option>
            @foreach ($filters['categories'] as $c)
                <option value="{{ $c->slug }}" @selected($selected['category'] === $c->slug)>{{ $c->name }}</option>
            @endforeach
        </select>
    @endunless

    <select name="desert" class="field" aria-label="Desert">
        <option value="">All deserts</option>
        @foreach ($filters['deserts'] as $d)
            <option value="{{ $d->slug }}" @selected($selected['desert'] === $d->slug)>{{ $d->name }}</option>
        @endforeach
    </select>

    <select name="duration" class="field" aria-label="Duration">
        @foreach ($durations as $value => $label)
            <option value="{{ $value }}" @selected($selected['duration'] === $value)>{{ $label }}</option>
        @endforeach
    </select>

    <input type="number" min="0" name="min_price" value="{{ $selected['min_price'] }}" placeholder="Min price" class="field" aria-label="Minimum price">
    <input type="number" min="0" name="max_price" value="{{ $selected['max_price'] }}" placeholder="Max price" class="field" aria-label="Maximum price">

    <select name="rating" class="field" aria-label="Rating">
        <option value="">Any rating</option>
        @foreach ([5, 4, 3] as $r)
            <option value="{{ $r }}" @selected($selected['rating'] === $r)>{{ $r }}+ stars</option>
        @endforeach
    </select>

    <select name="sort" class="field" aria-label="Sort by">
        @foreach ($sorts as $value => $label)
            <option value="{{ $value }}" @selected($selected['sort'] === $value)>{{ $label }}</option>
        @endforeach
    </select>

    <div class="flex gap-2 sm:col-span-2 lg:col-span-4">
        <button class="btn btn-primary">Apply filters</button>
        <a href="{{ $action }}" class="btn btn-outline">Reset</a>
    </div>
</form>

<p class="mb-4 text-sm text-stone-600">{{ number_format($tours->total()) }} {{ \Illuminate\Support\Str::plural('tour', $tours->total()) }} found</p>

@if ($tours->isEmpty())
    <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-stone-200">
        <p class="font-semibold text-stone-900">No tours match your filters.</p>
        <p class="mt-1 text-sm text-stone-600">Try removing a filter, or <a href="{{ route('contact') }}" class="text-amber-700 underline">contact us</a> and we will plan something for you.</p>
    </div>
@else
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($tours as $tour)
            <x-tour-card :tour="$tour" />
        @endforeach
    </div>
@endif

<div class="mt-8">{{ $tours->links() }}</div>