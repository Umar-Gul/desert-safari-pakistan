@php
    $titleSection = trim($__env->yieldContent('title'));
    $fullTitle = $titleSection !== '' ? $titleSection.' | '.$site->site_name : ($site->meta_title ?: $site->site_name);
    $description = trim($__env->yieldContent('meta_description')) ?: $site->meta_description;
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogImage = trim($__env->yieldContent('og_image')) ?: ($site->default_og_image ? asset($site->default_og_image) : null);
    $robots = trim($__env->yieldContent('robots')) ?: 'index,follow';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    @if ($site->favicon)
        <link rel="icon" href="{{ asset('storage/'.$site->favicon) }}">
    @endif

    {{-- Open Graph / Twitter --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $site->site_name }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    @if ($site->twitter_handle)
        <meta name="twitter:site" content="{{ $site->twitter_handle }}">
    @endif
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts.partials.jsonld-global')
    @stack('jsonld')
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-stone-50 font-sans text-stone-800 antialiased">

    @include('layouts.partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('layouts.partials.footer')
    @include('layouts.partials.whatsapp-float')
    @include('layouts.partials.cookie-banner')

    @stack('scripts')
</body>
</html>
