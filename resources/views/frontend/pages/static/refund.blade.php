@extends('layouts.frontend')

@section('title', 'Cancellation & Refund Policy')
@section('meta_description', 'Cancellation and refund rules for desert safari bookings with '.$site->site_name.'.')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12">
    <h1 class="mb-2 text-3xl font-extrabold text-stone-900">Cancellation &amp; Refund Policy</h1>
    <p class="mb-6 text-sm text-stone-500">Last updated: {{ date('F Y') }}</p>

    <div class="legal">
        <h2>General rules</h2>
        <ul>
            {{-- Hours and fee come from settings, so this stays correct if you change them in the dashboard. --}}
            <li>Free cancellation up to <strong>{{ setting('free_cancellation_hours', 24) }} hours</strong> before the tour start time.</li>
            <li>Cancellations made later than that may be charged <strong>{{ setting('late_cancellation_fee_percent', 25) }}%</strong> of the booking total.</li>
            <li>No-shows are not eligible for a refund.</li>
        </ul>

        <h2>Cancelled or changed by us</h2>
        <p>If we cancel or reschedule a tour because of weather, safety or security reasons, you can choose a new date at no extra cost or receive a full refund of the amount paid.</p>

        <h2>How refunds are paid</h2>
        <p>Approved refunds are returned using the original payment method where possible (bank transfer, JazzCash, Easypaisa or card) within 7 to 14 working days. Bank or gateway charges, if any, may be deducted.</p>

        <h2>Tour-specific policies</h2>
        <p>Some tours, such as private camps and events, may have their own policy shown on the tour page under "Cancellation Policy". That policy applies in addition to this page.</p>

        <h2>How to cancel</h2>
        <p>Contact us with your booking reference through the <a href="{{ route('contact') }}">contact page</a>@if ($site->whatsapp_number) or <a href="{{ whatsapp_url('Hello, I would like to cancel or change my booking.') }}" target="_blank" rel="noopener">WhatsApp</a>@endif.</p>
    </div>
</div>
@endsection