@extends('layouts.frontend')

@section('title', 'Privacy Policy')
@section('meta_description', 'How '.$site->site_name.' collects, uses and protects your personal information.')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12">
    <h1 class="mb-2 text-3xl font-extrabold text-stone-900">Privacy Policy</h1>
    <p class="mb-6 text-sm text-stone-500">Last updated: {{ date('F Y') }}</p>

    <div class="legal">
        <h2>1. Information we collect</h2>
        <ul>
            <li>Booking details: name, email, phone/WhatsApp, nationality, pickup location, group size and special requests.</li>
            <li>Payment information such as method, amount and transaction reference. We do not store full card numbers on our servers.</li>
            <li>Messages you send through our contact, question and review forms.</li>
            <li>Technical data such as IP address, browser type and pages visited.</li>
        </ul>

        <h2>2. How we use it</h2>
        <ul>
            <li>To process bookings, arrange pickups and contact you about your trip.</li>
            <li>To send confirmations, receipts and service messages.</li>
            <li>To respond to enquiries and publish approved reviews and answers.</li>
            <li>To prevent fraud and spam and to improve our website.</li>
        </ul>

        <h2>3. Sharing</h2>
        <p>We share information only with service providers needed to deliver your trip or process payments (for example drivers, camp partners, payment providers and email services), or when required by law. We do not sell personal data.</p>

        <h2>4. Retention</h2>
        <p>We keep booking and payment records as long as needed for service, accounting and legal purposes, then delete or anonymise them.</p>

        <h2>5. Security</h2>
        <p>We use access controls, encrypted connections and other reasonable safeguards. No online system is completely secure, so please protect your own account details and devices.</p>

        <h2>6. Your choices</h2>
        <p>You may request access to, correction of or deletion of your personal data by contacting us. You can manage cookies as described in our <a href="{{ route('cookies') }}">Cookie Policy</a>.</p>

        <h2>7. Reviews and questions</h2>
        <p>Your name, country and comment may be displayed publicly when a review or question is approved. Your email address is never shown.</p>

        <h2>8. Contact</h2>
        <p>
            For privacy requests, email
            @if ($site->email)<a href="mailto:{{ $site->email }}">{{ $site->email }}</a>@else us via the <a href="{{ route('contact') }}">contact page</a>@endif.
        </p>
    </div>
</div>
@endsection