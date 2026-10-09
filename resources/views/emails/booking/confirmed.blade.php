<x-mail::message>
# Your booking is confirmed

Hello {{ $firstName }}, good news: your booking for **{{ $booking->tour?->title }}** is confirmed.

<x-mail::panel>
**Reference:** {{ $booking->reference }}

**Date:** {{ $booking->tour_date->format('l, d F Y') }}{{ $booking->pickup_time ? ' at '.$booking->pickup_time : '' }}

**Pickup:** {{ $booking->pickup_location ?: 'We will contact you with the pickup point' }}{{ $booking->city ? ', '.$booking->city : '' }}

**Guests:** {{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)

**Total:** {{ $booking->currency }} {{ number_format((float) $booking->grand_total) }}

**Balance due:** {{ $booking->currency }} {{ number_format($booking->balanceDue()) }}
</x-mail::panel>

@if ($booking->balanceDue() > 0)
## How to pay

You can pay by {{ count($methods) ? implode(', ', $methods) : 'bank transfer, JazzCash, Easypaisa, card or cash' }}. Please send the payment receipt on WhatsApp or reply to this email.

@if ($bank !== '')
{!! nl2br(e($bank)) !!}
@endif

@endif
Your voucher is attached as a PDF. Please show it to your guide.

<x-mail::button :url="$trackUrl">
View my booking
</x-mail::button>

Need to change something? [Message us on WhatsApp]({{ $whatsappUrl }}).

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>