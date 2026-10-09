@extends('layouts.frontend')

@section('title', 'Cookie Policy')
@section('meta_description', 'How '.$site->site_name.' uses cookies and similar technologies.')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12">
    <h1 class="mb-2 text-3xl font-extrabold text-stone-900">Cookie Policy</h1>
    <p class="mb-6 text-sm text-stone-500">Last updated: {{ date('F Y') }}</p>

    <div class="legal">
        <h2>What are cookies?</h2>
        <p>Cookies are small text files stored on your device that help a website remember information about your visit.</p>

        <h2>Cookies we use</h2>
        <ul>
            <li><strong>Essential:</strong> session and security cookies needed for forms, bookings and login to work (for example CSRF protection).</li>
            <li><strong>Preferences:</strong> remembers your cookie choice.</li>
            <li><strong>Analytics (optional):</strong> helps us understand which pages are useful. These are used only if you choose "Accept all".</li>
        </ul>

        <h2>Managing cookies</h2>
        <p>You can change your choice at any time by clearing this site's data in your browser. You can also block or delete cookies in your browser settings, but some parts of the site may stop working.</p>

        <h2>More information</h2>
        <p>See our <a href="{{ route('privacy') }}">Privacy Policy</a> or <a href="{{ route('contact') }}">contact us</a>.</p>
    </div>
</div>
@endsection