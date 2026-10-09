@props(['title' => 'Admin'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-stone-100 text-stone-800 antialiased"
      x-data="{ sidebar: false }" @keydown.escape.window="sidebar = false">

    <div x-show="sidebar" x-cloak class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="sidebar = false"></div>

    <aside class="fixed inset-y-0 left-0 z-40 w-64 overflow-y-auto bg-stone-900 text-stone-300 transition-transform lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
        <x-admin.sidebar />
    </aside>

    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-stone-200 bg-white px-4 py-3 sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button" class="rounded-md p-2 text-stone-600 hover:bg-stone-100 lg:hidden" @click="sidebar = true" aria-label="Open menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('home') }}" target="_blank" class="text-sm text-stone-500 hover:text-amber-700">View site &rarr;</a>
            </div>

            <div class="flex items-center gap-4 text-sm">
                <span class="hidden text-right sm:block">
                    <span class="block font-medium text-stone-800">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-stone-500">{{ str(auth()->user()->getRoleNames()->first() ?? 'staff')->headline() }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 hover:bg-stone-50">Log out</button>
                </form>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @if ($errors->has('booking'))
                <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('booking') }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <x-admin.confirm-delete />

    @stack('scripts')
</body>
</html>