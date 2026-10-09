<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Dashboard') | {{ $site->site_name }} Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">
<div x-data="{ sidebar: false }" class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform overflow-y-auto bg-stone-900 text-stone-300 transition lg:static lg:translate-x-0"
           :class="{ 'translate-x-0': sidebar }">
        <div class="flex h-16 items-center px-5 text-lg font-bold text-white">{{ $site->site_name }}</div>

        <nav class="px-3 pb-8 text-sm" aria-label="Admin">
            @foreach (config('admin_nav') as $section => $items)
                @php
                    $visible = collect($items)->filter(fn ($i) =>
                        Route::has($i['route']) && (! $i['permission'] || auth()->user()->can($i['permission']))
                    );
                @endphp
                @if ($visible->isNotEmpty())
                    <p class="mb-1 mt-5 px-2 text-xs font-semibold uppercase tracking-wider text-stone-500">{{ $section }}</p>
                    @foreach ($visible as $item)
                        <a href="{{ route($item['route']) }}"
                           class="block rounded-md px-3 py-2 {{ request()->routeIs(Str::beforeLast($item['route'], '.').'*') ? 'bg-amber-600 text-white' : 'hover:bg-stone-800' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                @endif
            @endforeach
        </nav>
    </aside>

    <div x-show="sidebar" x-cloak class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="sidebar = false"></div>

    {{-- Main --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex h-16 items-center justify-between border-b border-stone-200 bg-white px-4">
            <div class="flex items-center gap-3">
                <button type="button" class="rounded-md p-2 lg:hidden" @click="sidebar = true" aria-label="Open sidebar">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold">@yield('heading', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" target="_blank" class="text-stone-600 hover:text-amber-700">View site</a>
                <span class="hidden text-stone-500 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-600 hover:text-red-600">Log out</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>