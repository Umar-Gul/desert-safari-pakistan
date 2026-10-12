<!-- Topbar: White Background with Contact Info & Utility Links -->
<div class="bg-white border-b border-gray-100 text-xs text-stone-700">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2.5">
        <!-- Left Side: Logo & Contacts -->
        <div class="flex items-center gap-6">
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}">
                    <x-application-logo class="block h-8 w-auto fill-current text-gray-800" />
                </a>
            </div>
            <div class="hidden md:flex items-center gap-5">
                @if ($site->phone)
                    <a href="{{ phone_href($site->phone) }}" class="flex items-center gap-1.5 hover:text-[#8B2014] transition">
                        <svg class="w-3.5 h-3.5 text-[#8B2014]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $site->phone }}</span>
                    </a>
                @endif
                @if ($site->whatsapp_number)
                    <a href="{{ whatsapp_url('Hello, I would like to know more about your desert safari tours.') }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-[#8B2014] transition">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                @endif
                @if ($site->email)
                    <a href="mailto:{{ $site->email }}" class="flex items-center gap-1.5 hover:text-[#8B2014] transition">
                        <svg class="w-3.5 h-3.5 text-[#8B2014]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ $site->email }}</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Right Side: Utility Navigation Links & Profile Dropdown -->
        <div class="flex items-center gap-6">
            <div class="hidden lg:flex items-center gap-6 font-medium text-stone-700">
                <a href="{{ route('home') }}" class="transition hover:text-[#8B2014] {{ request()->routeIs('home') ? 'text-[#8B2014]' : '' }}">Home</a>
                {{-- About is a static page in web.php; falls back to /about if the route name differs --}}
                <a href="{{ Route::has('about') ? route('about') : url('/about') }}" class="transition hover:text-[#8B2014] {{ request()->is('about') ? 'text-[#8B2014]' : '' }}">About</a>
                <a href="{{ route('contact') }}" class="transition hover:text-[#8B2014] {{ request()->routeIs('contact') ? 'text-[#8B2014]' : '' }}">Contact</a>
                <a href="{{ route('blog.index') }}" class="transition hover:text-[#8B2014] {{ request()->routeIs('blog.*') ? 'text-[#8B2014]' : '' }}">Blog</a>
                <a href="{{ route('gallery.index') }}" class="transition hover:text-[#8B2014] {{ request()->routeIs('gallery.*') ? 'text-[#8B2014]' : '' }}">Gallery</a>
                <a href="{{ route('tours.index') }}" class="transition hover:text-[#8B2014] {{ request()->routeIs('tours.*') ? 'text-[#8B2014]' : '' }}">All Activities</a>
            </div>

            @if ($site->tripadvisor_url)
                <a href="{{ $site->tripadvisor_url }}" target="_blank" rel="noopener" class="hidden sm:inline-flex items-center gap-1 text-xs font-semibold text-stone-800 hover:text-[#8B2014]">
                    Tripadvisor ★★★★★
                </a>
            @endif

            <!-- User Auth Dropdown -->
            @auth
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-1.5 border border-gray-200 text-xs font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none transition">
                                <div>{{ Auth::user()->name }}</div>
                                <svg class="fill-current h-3.5 w-3.5 ms-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('dashboard')">{{ __('Dashboard') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @endauth
        </div>
    </div>
</div>