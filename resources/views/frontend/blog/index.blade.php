@extends('layouts.frontend')

@section('title', $category ? $category->name.' articles' : 'Desert Travel Blog')
@section('meta_description', 'Travel guides, tips and stories from the deserts of Pakistan.')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <x-breadcrumbs :items="$category ? [['Blog', route('blog.index')], [$category->name, null]] : [['Blog', null]]" />
    <h1 class="mb-6 mt-3 text-3xl font-extrabold text-stone-900">{{ $category ? $category->name : 'Desert travel blog' }}</h1>

    <div class="grid gap-8 lg:grid-cols-4">
        <div class="lg:col-span-3">
            @if ($posts->isEmpty())
                <p class="text-stone-600">No articles yet. Please check back soon.</p>
            @else
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($posts as $post)<x-home.blog-card :post="$post" />@endforeach
                </div>
                <div class="mt-8">{{ $posts->links() }}</div>
            @endif
        </div>

        <aside class="h-fit rounded-2xl bg-white p-5 shadow-sm ring-1 ring-stone-200">
            <h2 class="mb-3 font-bold text-stone-900">Categories</h2>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('blog.index') }}" class="{{ $category ? 'text-stone-700' : 'font-semibold text-amber-700' }} hover:text-amber-700">All articles</a></li>
                @foreach ($categories as $cat)
                    <li><a href="{{ route('blog.category', $cat->slug) }}" class="{{ $category?->id === $cat->id ? 'font-semibold text-amber-700' : 'text-stone-700' }} hover:text-amber-700">{{ $cat->name }} <span class="text-stone-400">({{ $cat->blogs_count }})</span></a></li>
                @endforeach
            </ul>
        </aside>
    </div>
</div>
@endsection