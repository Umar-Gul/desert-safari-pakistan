@extends('layouts.frontend')

@section('title', 'Terms & Conditions')
@section('meta_description', 'Terms and conditions for booking desert safari tours with '.$site->site_name.'.')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12">
    <h1 class="mb-2 text-3xl font-extrabold text-stone-900">Terms &amp; Conditions</h1>
    <p class="mb-6 text-sm text-stone-500">Last updated: {{ date('F Y') }}</p>

    <div class="legal">
        <h2>1. Agreement</h2>
        <p>By booking a tour or using this website you agree to these terms. If you do not agree, please do not make a booking.</p>

        <h2>2. Bookings and confirmation</h2>
        <p>A booking is requested through our website and is confirmed only when we send a confirmation by email or WhatsApp. Availability is limited per date and is checked at the time of booking.</p>

        <h2>3. Prices and payment</h2>
        <p>Prices are shown in Pakistani Rupees (PKR) and are calculated on the server at checkout. Payment methods include bank transfer, JazzCash, Easypaisa, card (via payment link) and cash on arrival where offered. We may require an advance payment to hold your booking.</p>

        <h2>4. Cancellations and refunds</h2>
        <p>Cancellations are handled according to our <a href="{{ route('refund') }}">Cancellation &amp; Refund Policy</a> and any tour-specific policy shown on the tour page.</p>

        <h2>5. Health, safety and conduct</h2>
        <ul>
            <li>Follow the instructions of guides and drivers at all times, especially during dune driving and animal rides.</li>
            <li>Guests with heart, back, neck or pregnancy-related conditions should consult a doctor before joining active tours.</li>
            <li>Minimum age and group size limits shown on each tour apply.</li>
            <li>Alcohol, drugs and any unlawful activity are not permitted on our tours.</li>
            <li>Respect local customs, dress modestly where requested and do not harm wildlife or the environment.</li>
        </ul>

        <h2>6. Weather and changes</h2>
        <p>Desert conditions can change. We may adjust routes, timings or activities, or reschedule a tour, for safety reasons such as extreme heat, sandstorms or security advisories. If we cancel a tour, you may choose a new date or a refund of amounts paid.</p>

        <h2>7. Liability</h2>
        <p>We take reasonable care to provide safe services. To the extent permitted by law, we are not liable for loss or damage caused by events outside our control, or for personal belongings left in vehicles or camps.</p>

        <h2>8. Intellectual property</h2>
        <p>Content on this website, including text, images and logos, belongs to {{ $site->site_name }} or its licensors and may not be reused without permission.</p>

        <h2>9. Governing law</h2>
        <p>These terms are governed by the laws of Pakistan.</p>

        <h2>10. Contact</h2>
        <p>Questions about these terms? <a href="{{ route('contact') }}">Contact us</a>.</p>
    </div>
</div>
@endsection