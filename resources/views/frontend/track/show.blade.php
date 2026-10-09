@extends('layouts.frontend')

@section('title', 'Track my booking')
@section('robots', 'noindex,nofollow')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-3xl font-extrabold text-stone-900">Track my booking</h1>
    <p class="mt-1 text-stone-600">Enter your booking reference and the email or phone number you booked with.</p>

    @if ($notice)
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ $notice }}</div>
    @endif
    <x-flash class="mt-4" />

    <form method="POST" action="{{ route('track.lookup') }}" class="mt-6 grid gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200 sm:grid-cols-2">
        @csrf
        <div>
            <label class="field-label" for="reference">Booking reference</label>
            <input id="reference" name="reference" type="text" value="{{ old('reference', $booking?->reference) }}" placeholder="DSP-2026-000123" required class="field">
            @error('reference')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="field-label" for="contact">Email or phone</label>
            <input id="contact" name="contact" type="text" value="{{ old('contact', $contact ?? '') }}" required class="field">
            @error('contact')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2"><button class="btn btn-primary">Find my booking</button></div>
    </form>

    @if ($booking)
        @php $money = fn ($n) => $booking->currency.' '.number_format((float) $n); @endphp

        <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-stone-900">{{ $booking->reference }}</h2>
                <div class="flex gap-2">
                    <x-admin.badge :color="$booking->status->color()" :label="$booking->status->label()" />
                    <x-admin.badge :color="$booking->payment_status->color()" :label="'Payment: '.$booking->payment_status->label()" />
                </div>
            </div>

            <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div><dt class="text-stone-500">Tour</dt><dd class="font-medium">{{ $booking->tour?->title }}</dd></div>
                <div><dt class="text-stone-500">Date</dt><dd class="font-medium">{{ $booking->tour_date->format('l, d M Y') }}{{ $booking->pickup_time ? ' at '.$booking->pickup_time : '' }}</dd></div>
                <div><dt class="text-stone-500">Guests</dt><dd class="font-medium">{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</dd></div>
                <div><dt class="text-stone-500">Pickup</dt><dd class="font-medium">{{ $booking->pickup_location ?: 'To be confirmed' }}{{ $booking->city ? ', '.$booking->city : '' }}</dd></div>
                @if ($booking->campType)<div><dt class="text-stone-500">Camp</dt><dd class="font-medium">{{ $booking->campType->name }}</dd></div>@endif
                @if ($booking->vehicle)<div><dt class="text-stone-500">Vehicle</dt><dd class="font-medium">{{ $booking->vehicle->name }}</dd></div>@endif
                @foreach ($booking->addons as $line)<div><dt class="text-stone-500">Add-on</dt><dd class="font-medium">{{ $line->name_snapshot }} &times; {{ $line->quantity }}</dd></div>@endforeach
                <div><dt class="text-stone-500">Total</dt><dd class="font-medium">{{ $money($booking->grand_total) }}</dd></div>
                <div><dt class="text-stone-500">Paid</dt><dd class="font-medium">{{ $money($booking->paidAmount()) }}</dd></div>
                <div><dt class="text-stone-500">Balance due</dt><dd class="font-medium">{{ $money($booking->balanceDue()) }}</dd></div>
            </dl>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ whatsapp_url('Hello, I have a question about booking '.$booking->reference) }}" target="_blank" rel="noopener" class="btn btn-whatsapp">Contact us on WhatsApp</a>
                @if (in_array($booking->status, [\App\Enums\BookingStatus::Confirmed, \App\Enums\BookingStatus::Completed], true))
                    <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('booking.voucher', now()->addHour(), ['reference' => $booking->reference]) }}" class="btn btn-outline">Download voucher (PDF)</a>
                @endif
            </div>

            @if ($quote)
                <form method="POST" action="{{ route('track.cancel') }}" class="mt-6 rounded-xl bg-stone-50 p-4 text-sm"
                      onsubmit="return confirm('Do you really want to cancel this booking?')">
                    @csrf
                    <input type="hidden" name="reference" value="{{ $booking->reference }}">
                    <input type="hidden" name="contact" value="{{ $contact ?? '' }}">
                    <p class="font-semibold text-stone-900">Need to cancel?</p>
                    <p class="mt-1 text-stone-600">
                        @if ($quote->isFree)
                            Cancelling now is free of charge.
                        @else
                            Cancelling now carries a fee of {{ rtrim(rtrim(number_format($quote->feePercent, 1), '0'), '.') }}% ({{ $money($quote->feeAmount) }}).
                        @endif
                    </p>
                    <button class="mt-3 rounded-md border border-red-300 bg-white px-4 py-2 font-medium text-red-700 hover:bg-red-50">Cancel this booking</button>
                </form>
            @endif
        </section>
    @endif
</div>
@endsection