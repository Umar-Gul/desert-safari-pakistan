@extends('layouts.frontend')

@section('title', 'Photo & Video Gallery')
@section('meta_description', 'Photos and videos from our desert safaris, camps and events in Pakistan.')

@php
    $lightbox = $items->getCollection()
        ->filter(fn ($i) => $i->type === \App\Enums\GalleryItemType::Image && $i->getFirstMedia('cover'))
        ->map(fn ($i) => ['id' => $i->id, 'src' => $i->getFirstMediaUrl('cover', 'large'), 'alt' => (string) ($i->title ?: $i->coverAlt())])
        ->values();
@endphp

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10"
     x-data="{ open: false, i: 0, items: @js($lightbox), show(id) { this.i = Math.max(0, this.items.findIndex(x => x.id === id)); this.open = true; } }"
     @keydown.escape.window="open = false"
     @keydown.arrow-right.window="if (open) i = (i + 1) % items.length"
     @keydown.arrow-left.window="if (open) i = (i - 1 + items.length) % items.length">

    <x-breadcrumbs :items="[['Gallery', null]]" />
    <h1 class="mb-6 mt-3 text-3xl font-extrabold text-stone-900">Gallery</h1>

    @if ($categories->isNotEmpty())
        <div class="mb-6 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('gallery.index') }}" class="rounded-full px-4 py-1.5 {{ $category === '' ? 'bg-amber-600 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-200' }}">All</a>
            @foreach ($categories as $name)
                <a href="{{ route('gallery.index', ['category' => $name]) }}" class="rounded-full px-4 py-1.5 {{ $category === $name ? 'bg-amber-600 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-200' }}">{{ $name }}</a>
            @endforeach
        </div>
    @endif

    @if ($items->isEmpty())
        <p class="text-stone-600">Photos are coming soon.</p>
    @else
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($items as $item)
                @if ($item->type === \App\Enums\GalleryItemType::Video && $item->url)
                    <a href="{{ $item->url }}" target="_blank" rel="noopener" class="group relative block aspect-square overflow-hidden rounded-xl bg-stone-800">
                        <x-cover :model="$item" class="h-full w-full object-cover opacity-80" />
                        <span class="absolute inset-0 flex items-center justify-center text-4xl text-white">&#9654;</span>
                    </a>
                @else
                    <button type="button" @click="show({{ $item->id }})" class="group relative block aspect-square overflow-hidden rounded-xl bg-stone-200 text-left">
                        <x-cover :model="$item" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                        <span class="sr-only">Open {{ $item->title ?: 'photo' }}</span>
                    </button>
                @endif
            @endforeach
        </div>
        <div class="mt-8">{{ $items->links() }}</div>
    @endif

    {{-- Lightbox --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4" @click.self="open = false" role="dialog" aria-modal="true">
        <button type="button" class="absolute right-4 top-4 text-3xl text-white" @click="open = false" aria-label="Close">&times;</button>
        <button type="button" class="absolute left-3 text-4xl text-white" @click="i = (i - 1 + items.length) % items.length" aria-label="Previous">&lsaquo;</button>
        <template x-if="items[i]"><img :src="items[i].src" :alt="items[i].alt" class="max-h-[85vh] max-w-full rounded-lg object-contain"></template>
        <button type="button" class="absolute right-3 text-4xl text-white" @click="i = (i + 1) % items.length" aria-label="Next">&rsaquo;</button>
    </div>
</div>
@endsection