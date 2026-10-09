<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Voucher {{ $booking->reference }}</title>
    <style>
        body { font-family: system-ui, Arial, sans-serif; color: #292524; margin: 0; background: #f5f5f4; }
        .page { max-width: 760px; margin: 24px auto; background: #fff; padding: 36px; border: 1px solid #e7e5e4; }
        h1 { margin: 0; font-size: 22px; color: #b45309; }
        h2 { font-size: 14px; text-transform: uppercase; letter-spacing: .05em; color: #78716c; margin: 24px 0 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        td { padding: 6px 0; vertical-align: top; border-bottom: 1px solid #f5f5f4; }
        td:first-child { width: 36%; color: #78716c; }
        .ref { font-size: 26px; font-weight: 700; letter-spacing: .03em; margin: 12px 0 0; }
        .actions { max-width: 760px; margin: 16px auto 0; text-align: right; }
        .actions button { padding: 8px 16px; background: #d97706; color: #fff; border: 0; border-radius: 6px; cursor: pointer; }
        .note { font-size: 12px; color: #78716c; margin-top: 28px; }
        @media print { body { background: #fff; } .page { border: 0; margin: 0; padding: 0; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button onclick="window.print()">Print / save as PDF</button></div>

    <div class="page">
        <h1>{{ \App\Models\Setting::get('site_name', config('app.name')) }}</h1>
        <p style="margin:4px 0 0;font-size:13px;color:#78716c">
            {{ \App\Models\Setting::get('phone') }} {{ \App\Models\Setting::get('email') ? ' | '.\App\Models\Setting::get('email') : '' }}
        </p>

        <p class="ref">{{ $booking->reference }}</p>
        <p style="margin:0;font-size:13px;color:#78716c">Booking voucher - status: {{ $booking->status->label() }}</p>

        <h2>Trip</h2>
        <table>
            <tr><td>Tour</td><td><strong>{{ $booking->tour?->title }}</strong></td></tr>
            <tr><td>Date</td><td>{{ $booking->tour_date->format('l, d F Y') }}</td></tr>
            <tr><td>Pickup</td><td>{{ $booking->pickup_time ?: 'To be confirmed' }} {{ $booking->pickup_location ? '- '.$booking->pickup_location : '' }}{{ $booking->city ? ', '.$booking->city : '' }}</td></tr>
            <tr><td>Guests</td><td>{{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)</td></tr>
            @if ($booking->campType)<tr><td>Camp</td><td>{{ $booking->campType->name }}</td></tr>@endif
            @if ($booking->vehicle)<tr><td>Vehicle</td><td>{{ $booking->vehicle->name }}</td></tr>@endif
            @foreach ($booking->addons as $line)
                <tr><td>Add-on</td><td>{{ $line->name_snapshot }} &times; {{ $line->quantity }}</td></tr>
            @endforeach
        </table>

        <h2>Guest</h2>
        <table>
            <tr><td>Name</td><td>{{ $booking->customer_name }}</td></tr>
            <tr><td>Phone</td><td>{{ $booking->phone }}</td></tr>
            <tr><td>Email</td><td>{{ $booking->email }}</td></tr>
            @if ($booking->special_requests)<tr><td>Requests</td><td>{{ $booking->special_requests }}</td></tr>@endif
        </table>

        <h2>Payment</h2>
        <table>
            <tr><td>Total</td><td><strong>{{ $booking->currency }} {{ number_format((float) $booking->grand_total) }}</strong></td></tr>
            <tr><td>Paid</td><td>{{ $booking->currency }} {{ number_format($booking->paidAmount()) }}</td></tr>
            <tr><td>Balance due</td><td>{{ $booking->currency }} {{ number_format($booking->balanceDue()) }}</td></tr>
        </table>

        <p class="note">Please show this voucher to your guide. Cancellation and refund rules are on our website. Thank you for travelling with us.</p>
    </div>
</body>
</html>