@extends('layouts.frontend')

@section('title', 'Booking received')
@section('robots', 'noindex,nofollow')

@php
    $money = fn ($n) => $booking->currency.' '.number_format((float) $n);
    $message = "Hello! I just booked {$booking->tour?->title} on {$booking->tour_date->format('d M Y')} for {$booking->guests_count} guest(s). My booking reference is {$booking->reference}. Please confirm my booking.";
    $bank = trim((string) setting('bank_details', ''));
@endphp

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12">
    <div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-stone-200">
        <p class="text-5xl text-green-600" aria-hidden="true">&#10003;</p>
        <h1 class="mt-2 text-3xl font-extrabold text-stone-900">Thank you, {{ \Illuminate\Support\Str::before($booking->customer_name, ' ') }}!</h1>
        <p class="mt-1 text-stone-600">Your booking request has been received and is <strong>pending confirmation</strong>.</p>

        <p class="mt-6 text-sm uppercase tracking-wide text-stone-500">Booking reference</p>
        <p class="text-3xl font-extrabold tracking-wider text-amber-700">{{ $booking->reference }}</p>
        <p class="mt-1 text-xs text-stone-500">Keep this reference. You will need it with your email or phone to track or cancel your booking.</p>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ whatsapp_url($message) }}" target="_blank" rel="noopener" class="btn btn-whatsapp">Confirm on WhatsApp</a>
            <a href="{{ route('track.form') }}" class="btn btn-outline">Track my booking</a>
            <button type="button" onclick="window.print()" class="btn btn-outline">Print</button>
        </div>
    </div>

    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
        <h2 class="mb-3 text-lg font-bold text-stone-900">Booking summary</h2>
        <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
            <div><dt class="text-stone-500">Tour</dt><dd class="font-medium">{{ $booking->tour?->title }}</dd></div>
            <div><dt class="text-stone-500">Date</dt><dd class="font-medium">{{ $booking->tour_date->format('l, d M Y') }}{{ $booking->pickup_time ? ' at '.$booking->pickup_time : '' }}</dd></div>
            <div><dt class="text-stone-500">Guests</dt><dd class="font-medium">{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</dd></div>
            <div><dt class="text-stone-500">Pickup</dt><dd class="font-medium">{{ $booking->pickup_location ?: 'To be confirmed' }}{{ $booking->city ? ', '.$booking->city : '' }}</dd></div>
            @if ($booking->campType)<div><dt class="text-stone-500">Camp</dt><dd class="font-medium">{{ $booking->campType->name }}</dd></div>@endif
            @if ($booking->vehicle)<div><dt class="text-stone-500">Vehicle</dt><dd class="font-medium">{{ $booking->vehicle->name }}</dd></div>@endif
            @foreach ($booking->addons as $line)
                <div><dt class="text-stone-500">Add-on</dt><dd class="font-medium">{{ $line->name_snapshot }} &times; {{ $line->quantity }}</dd></div>
            @endforeach
            <div class="sm:col-span-2 border-t border-stone-100 pt-2"><dt class="text-stone-500">Total</dt><dd class="text-xl font-extrabold text-stone-900">{{ $money($booking->grand_total) }}</dd></div>
        </dl>
    </section>

    <section class="mt-6 rounded-2xl bg-amber-50 p-6 text-sm text-stone-700">
        <h2 class="mb-2 text-lg font-bold text-stone-900">What happens next</h2>
        <ol class="list-decimal space-y-1 pl-5">
            <li>We check your booking and confirm it by email or WhatsApp.</li>
            <li>We send payment details. You can pay by {{ count($methods) ? implode(', ', $methods) : 'bank transfer, JazzCash, Easypaisa, card or cash' }}.</li>
            <li>Our driver contacts you before pickup.</li>
        </ol>
        @if ($bank !== '')
            <div class="mt-4 rounded-lg bg-white p-4"><p class="font-semibold text-stone-900">Payment details</p><p class="mt-1 whitespace-pre-line">{{ $bank }}</p></div>
        @endif
    </section>
</div>
@endsection