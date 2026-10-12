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

<!-- Main navbar: crimson #8B2014, centered safari categories + mobile drawer -->
<div x-data="{ open: false }" @keydown.escape.window="open = false" class="bg-[#8B2014] text-white">
    <div class="mx-auto max-w-7xl px-4">

        <!-- Desktop: centered categories -->
        <nav class="hidden items-center justify-center gap-1 py-1.5 lg:flex" aria-label="Safari categories">
            @foreach ($navCategories as $cat)
                @php $isActive = request()->is('category/' . $cat->slug); @endphp
                <a href="{{ route('categories.show', $cat->slug) }}"
                   @if ($isActive) aria-current="page" @endif
                   class="whitespace-nowrap rounded-md px-4 py-2.5 text-sm font-medium transition duration-200 hover:bg-white/15 {{ $isActive ? 'bg-white/20' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        <!-- Mobile / tablet: toggle bar -->
        <div class="flex items-center justify-between py-2.5 lg:hidden">
            <span class="text-sm font-semibold tracking-wide">Explore Safaris</span>
            <button @click="open = !open" type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 transition hover:bg-white/15 focus:outline-none"
                    :aria-expanded="open.toString()" aria-controls="mobile-menu" aria-label="Toggle menu">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile drawer -->
    <div id="mobile-menu" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="max-h-[75vh] overflow-y-auto border-t border-white/15 bg-[#7a1b11] lg:hidden">

        <div class="mx-auto max-w-7xl space-y-1 px-4 py-3">
            @foreach ($navCategories as $cat)
                <a href="{{ route('categories.show', $cat->slug) }}"
                   class="block rounded-md px-3 py-2.5 text-sm font-medium transition hover:bg-white/15 {{ request()->is('category/' . $cat->slug) ? 'bg-white/20' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>

        <div class="mx-auto max-w-7xl border-t border-white/15 px-4 py-3">
            <div class="grid grid-cols-2 gap-1">
                @foreach ($utilityLinks as $link)
                    <a href="{{ $link['href'] }}"
                       class="rounded-md px-3 py-2 text-sm transition hover:bg-white/15 {{ $link['active'] ? 'bg-white/20 text-white' : 'text-white/90' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($site->phone || $site->whatsapp_number || $site->email || $site->tripadvisor_url)
            <div class="mx-auto max-w-7xl space-y-1 border-t border-white/15 px-4 py-3 text-sm text-white/90">
                @if ($site->phone)
                    <a href="{{ phone_href($site->phone) }}" class="block rounded-md px-3 py-2 hover:bg-white/15">Call: {{ $site->phone }}</a>
                @endif
                @if ($site->whatsapp_number)
                    <a href="{{ whatsapp_url('Hello, I would like to know more about your desert safari tours.') }}" target="_blank" rel="noopener" class="block rounded-md px-3 py-2 hover:bg-white/15">WhatsApp us</a>
                @endif
                @if ($site->email)
                    <a href="mailto:{{ $site->email }}" class="block truncate rounded-md px-3 py-2 hover:bg-white/15">{{ $site->email }}</a>
                @endif
                @if ($site->tripadvisor_url)
                    <a href="{{ $site->tripadvisor_url }}" target="_blank" rel="noopener" class="block rounded-md px-3 py-2 hover:bg-white/15">Tripadvisor ★★★★★</a>
                @endif
            </div>
        @endif

        @auth
            <div class="mx-auto max-w-7xl border-t border-white/15 px-4 py-3">
                <div class="px-3 pb-2 text-xs text-white/70">{{ Auth::user()->name }}</div>
                <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-white/15">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-white/15">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-md px-3 py-2 text-left text-sm hover:bg-white/15">Log Out</button>
                </form>
            </div>
        @endauth
    </div>
</div>