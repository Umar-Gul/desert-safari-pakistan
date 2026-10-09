<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Voucher {{ $booking->reference }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #292524; }
    h1 { font-size: 20px; margin: 0; color: #b45309; }
    h2 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #78716c; margin: 22px 0 6px; border-bottom: 1px solid #e7e5e4; padding-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 5px 0; vertical-align: top; }
    td.label { width: 32%; color: #78716c; }
    .ref { font-size: 24px; font-weight: bold; letter-spacing: 1px; margin: 12px 0 2px; }
    .status { display: inline-block; padding: 2px 8px; background: #fef3c7; color: #92400e; font-size: 11px; }
    .small { font-size: 10px; color: #78716c; }
    .note { margin-top: 24px; font-size: 10px; color: #78716c; }
</style>
</head>
<body>
    <h1>{{ \App\Models\Setting::get('site_name', config('app.name')) }}</h1>
    <p class="small">
        {{ \App\Models\Setting::get('phone') }}
        {{ \App\Models\Setting::get('email') ? ' | '.\App\Models\Setting::get('email') : '' }}
        {{ \App\Models\Setting::get('address') ? ' | '.\App\Models\Setting::get('address') : '' }}
    </p>

    <p class="ref">{{ $booking->reference }}</p>
    <p><span class="status">{{ $booking->status->label() }}</span> <span class="small">Booking voucher</span></p>

    <h2>Trip</h2>
    <table>
        <tr><td class="label">Tour</td><td><strong>{{ $booking->tour?->title }}</strong></td></tr>
        <tr><td class="label">Date</td><td>{{ $booking->tour_date->format('l, d F Y') }}</td></tr>
        <tr><td class="label">Pickup</td><td>{{ $booking->pickup_time ?: 'To be confirmed' }}{{ $booking->pickup_location ? ' - '.$booking->pickup_location : '' }}{{ $booking->city ? ', '.$booking->city : '' }}</td></tr>
        <tr><td class="label">Guests</td><td>{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</td></tr>
        @if ($booking->campType)<tr><td class="label">Camp</td><td>{{ $booking->campType->name }}</td></tr>@endif
        @if ($booking->vehicle)<tr><td class="label">Vehicle</td><td>{{ $booking->vehicle->name }}</td></tr>@endif
        @foreach ($booking->addons as $line)
            <tr><td class="label">Add-on</td><td>{{ $line->name_snapshot }} x {{ $line->quantity }}</td></tr>
        @endforeach
    </table>

    <h2>Guest</h2>
    <table>
        <tr><td class="label">Name</td><td>{{ $booking->customer_name }}</td></tr>
        <tr><td class="label">Phone</td><td>{{ $booking->phone }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $booking->email }}</td></tr>
        @if ($booking->special_requests)<tr><td class="label">Requests</td><td>{{ $booking->special_requests }}</td></tr>@endif
    </table>

    <h2>Payment</h2>
    <table>
        <tr><td class="label">Total</td><td><strong>{{ $booking->currency }} {{ number_format((float) $booking->grand_total) }}</strong></td></tr>
        <tr><td class="label">Paid</td><td>{{ $booking->currency }} {{ number_format($booking->paidAmount()) }}</td></tr>
        <tr><td class="label">Balance due</td><td>{{ $booking->currency }} {{ number_format($booking->balanceDue()) }}</td></tr>
    </table>

    <p class="note">
        Please show this voucher to your guide. Free cancellation up to {{ config('booking.free_cancellation_hours', 24) }} hours before the start time;
        later cancellations may be charged {{ rtrim(rtrim(number_format((float) config('booking.late_cancellation_fee_percent', 50), 1), '0'), '.') }}% of the booking total.
        Thank you for travelling with us.
    </p>
</body>
</html>