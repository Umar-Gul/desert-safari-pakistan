@extends('layouts.frontend')

@section('title', $term !== '' ? 'Search: '.$term : 'Search tours')
@section('robots', 'noindex,follow')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="mb-6 text-3xl font-extrabold text-stone-900">{{ $term !== '' ? 'Results for "'.$term.'"' : 'Search tours' }}</h1>

    @if ($tours)
        @include('frontend.partials.tour-list', ['action' => route('search')])
    @else
        <form action="{{ route('search') }}" method="GET" class="flex max-w-xl gap-2">
            <input type="search" name="q" placeholder="Search desert safaris, camel rides, camping..." class="field flex-1" aria-label="Search tours" autofocus>
            <button class="btn btn-primary">Search</button>
        </form>

        <p class="mt-8 text-sm font-semibold uppercase tracking-wide text-stone-500">Or browse</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="rounded-full border border-stone-300 bg-white px-4 py-1.5 text-sm hover:border-amber-500 hover:text-amber-700">{{ $category->name }}</a>
            @endforeach
        </div>
    @endif
</div>
@endsection