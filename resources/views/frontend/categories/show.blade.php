@extends('layouts.frontend')

@section('title', $category->meta_title ?: $category->name)
@section('meta_description', $category->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $category->description), 155))
@section('canonical', $category->canonical ?: route('categories.show', $category->slug))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <x-breadcrumbs :items="[['All activities', route('tours.index')], [$category->name, null]]" />

    <header class="mb-8 mt-3 max-w-3xl">
        <h1 class="text-3xl font-extrabold text-stone-900">{{ $category->name }}</h1>
        @if ($category->description)<p class="mt-2 text-stone-600">{{ $category->description }}</p>@endif
    </header>

    @include('frontend.partials.tour-list', ['action' => route('categories.show', $category->slug), 'hideCategory' => true])
</div>
@endsection