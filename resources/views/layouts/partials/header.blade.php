@php
    $links = [
        ['Home', url('/'), request()->routeIs('home')],
        ['About', route('about'), request()->routeIs('about')],
        ['All Activities', url('/tours'), request()->is('tours*')],
        ['Blog', url('/blog'), request()->is('blog*')],
        ['Gallery', url('/gallery'), request()->is('gallery*')],
        ['Contact', route('contact'), request()->routeIs('contact')],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-stone-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            @if ($site->logo)
                <img src="{{ asset('storage/'.$site->logo) }}" alt="{{ $site->site_name }}" class="h-10 w-auto">
            @else
                <span class="text-xl font-extrabold tracking-tight text-amber-700">{{ $site->site_name }}</span>
            @endif
        </a>

        <nav class="hidden items-center gap-6 lg:flex" aria-label="Main">
            @foreach ($links as [$label, $href, $active])
                <a href="{{ $href }}"
                   class="text-sm font-medium {{ $active ? 'text-amber-700' : 'text-stone-700 hover:text-amber-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 lg:flex">
            <a href="{{ url('/tours') }}" class="btn btn-primary">Book Now</a>
        </div>

        <button type="button" class="rounded-md p-2 text-stone-700 lg:hidden" @click="open = !open"
                :aria-expanded="open" aria-label="Toggle menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path x-show="!open" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                <path x-show="open" x-cloak stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    {{-- Category quick links: populated from the DB in Phase 5 via $navCategories --}}
    @isset($navCategories)
        <div class="hidden border-t border-stone-100 bg-stone-50 lg:block">
            <div class="mx-auto flex max-w-7xl gap-5 overflow-x-auto px-4 py-2 text-xs">
                @foreach ($navCategories as $cat)
                    <a href="{{ url('/category/'.$cat->slug) }}" class="whitespace-nowrap text-stone-600 hover:text-amber-700">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>
    @endisset

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak x-transition class="border-t border-stone-200 bg-white lg:hidden">
        <nav class="space-y-1 px-4 py-3" aria-label="Mobile">
            @foreach ($links as [$label, $href, $active])
                <a href="{{ $href }}"
                   class="block rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">
                    {{ $label }}
                </a>
            @endforeach
            <a href="{{ url('/tours') }}" class="btn btn-primary mt-2 w-full">Book Now</a>
            @if ($site->phone)
                <a href="{{ phone_href($site->phone) }}" class="btn btn-outline w-full">Call {{ $site->phone }}</a>
            @endif
        </nav>
    </div>
</header>