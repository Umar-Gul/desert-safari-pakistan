<x-mail::message>
# Thank you, {{ $firstName }}!

We have received your booking request for **{{ $booking->tour?->title }}**. It is **pending confirmation**: our team will check availability and confirm by email or WhatsApp.

<x-mail::panel>
**Reference:** {{ $booking->reference }}

**Date:** {{ $booking->tour_date->format('l, d F Y') }}{{ $booking->pickup_time ? ' at '.$booking->pickup_time : '' }}

**Guests:** {{ $booking->adults }} adult(s), {{ $booking->children }} child(ren)

**Pickup:** {{ $booking->pickup_location ?: 'To be confirmed' }}{{ $booking->city ? ', '.$booking->city : '' }}

**Total:** {{ $booking->currency }} {{ number_format((float) $booking->grand_total) }}
</x-mail::panel>

No payment is needed yet. After we confirm, we will send payment details.

<x-mail::button :url="$trackUrl">
Track my booking
</x-mail::button>

Keep your reference **{{ $booking->reference }}**: you will need it with your email or phone number to track or cancel. Questions? [Message us on WhatsApp]({{ $whatsappUrl }}).

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>