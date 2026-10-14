@php
    $utilityLinks = [
        ['label' => 'Home',           'href' => route('home'),          'active' => request()->routeIs('home')],
        ['label' => 'About',          'href' => \Illuminate\Support\Facades\Route::has('about') ? route('about') : url('/about'), 'active' => request()->is('about')],
        ['label' => 'Contact',        'href' => route('contact'),       'active' => request()->routeIs('contact')],
        ['label' => 'Blog',           'href' => route('blog.index'),    'active' => request()->routeIs('blog.*')],
        ['label' => 'Gallery',        'href' => route('gallery.index'), 'active' => request()->routeIs('gallery.*')],
        ['label' => 'All Activities', 'href' => route('tours.index'),   'active' => request()->routeIs('tours.*')],
    ];
@endphp


<!-- Main navbar: glassmorphic crimson background, centered safari categories + mobile drawer -->
<div x-data="{ open: false }" @keydown.escape.window="open = false"
     class="border-b border-white/20 bg-[#8B2014]/75 text-white shadow-sm backdrop-blur-xl">

    <div class="mx-auto max-w-7xl px-4">

        <!-- Desktop: centered categories -->
        <nav class="hidden items-center justify-center gap-1 py-0.5 lg:flex" aria-label="Safari categories">
            @foreach ($navCategories as $cat)
                @php $isActive = request()->is('category/' . $cat->slug); @endphp
                <a href="{{ route('categories.show', $cat->slug) }}"
                   @if ($isActive) aria-current="page" @endif
                   class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium transition duration-200 hover:bg-white/15 {{ $isActive ? 'bg-white/20' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        <!-- Mobile / tablet: toggle bar -->
        <div class="flex items-center justify-between py-1.5 lg:hidden">
            <span class="text-sm font-semibold tracking-wide">Explore Safaris</span>
            <button @click="open = !open" type="button"
                    class="inline-flex items-center justify-center rounded-md p-1.5 transition hover:bg-white/15 focus:outline-none"
                    :aria-expanded="open.toString()" aria-controls="mobile-menu" aria-label="Toggle menu">
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>


