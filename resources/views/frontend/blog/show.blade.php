@extends('layouts.frontend')

@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($post->excerpt ?: $post->content)), 155))
@section('canonical', $post->canonical ?: route('blog.show', $post->slug))
@section('og_type', 'article')

@push('jsonld')
    <x-seo.jsonld :data="app(\App\Services\SeoService::class)->blogPosting($post)" />
@endpush
@if ($post->coverUrl('large'))
    @section('og_image', $post->coverUrl('large'))
@endif

@section('content')
<article class="mx-auto max-w-3xl px-4 py-10">
    <x-breadcrumbs :items="[['Blog', route('blog.index')], [$post->title, null]]" />

    <header class="mb-6 mt-3">
        <p class="text-sm text-stone-500">
            {{ $post->published_at?->format('d F Y') }}
            @if ($post->category) &middot; <a href="{{ route('blog.category', $post->category->slug) }}" class="text-amber-700 hover:underline">{{ $post->category->name }}</a>@endif
        </p>
        <h1 class="mt-1 text-3xl font-extrabold leading-tight text-stone-900 sm:text-4xl">{{ $post->title }}</h1>
    </header>

    <div class="mb-8 aspect-[16/9] overflow-hidden rounded-2xl bg-stone-100">
        <x-cover :model="$post" conversion="large" :eager="true" class="h-full w-full object-cover" />
    </div>

    <div class="rich">{!! \App\Support\Html::clean($post->content) !!}</div>

    <div class="mt-10 rounded-2xl bg-amber-50 p-6 text-center">
        <p class="font-semibold text-stone-900">Ready to see the desert yourself?</p>
        <a href="{{ route('tours.index') }}" class="btn btn-primary mt-3">Browse tours</a>
    </div>
</article>

@if ($related->isNotEmpty())
    <section class="mx-auto mb-10 max-w-7xl px-4">
        <h2 class="mb-4 text-xl font-bold text-stone-900">More to read</h2>
        <div class="grid gap-6 md:grid-cols-3">@foreach ($related as $item)<x-home.blog-card :post="$item" />@endforeach</div>
    </section>
@endif
@endsection