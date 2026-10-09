<x-mail::message>
# Booking cancelled

Hello {{ $firstName }}, your booking **{{ $booking->reference }}** for **{{ $booking->tour?->title }}** on {{ $booking->tour_date->format('d F Y') }} has been cancelled.

@if ($quote->isFree)
No cancellation fee applies.
@else
A cancellation fee of {{ $booking->currency }} {{ number_format($quote->feeAmount) }} ({{ rtrim(rtrim(number_format($quote->feePercent, 1), '0'), '.') }}%) applies. Our team will contact you about any refund that is due.
@endif

We hope to welcome you another time. [Message us on WhatsApp]({{ $whatsappUrl }}) if you would like to rebook.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>