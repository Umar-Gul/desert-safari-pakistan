<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Mail\BookingReceivedMail;
use App\Notifications\AdminAlert;
use App\Support\AdminMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingCreatedNotifications implements ShouldQueue
{
    public int $tries = 3;

    public function handle(BookingCreated $event): void
    {
        // A restored model has no relations loaded; load what the templates use.
        $booking = $event->booking->load(['tour', 'campType', 'vehicle', 'addons', 'payments']);

        Mail::to($booking->email, $booking->customer_name)->send(new BookingReceivedMail($booking));

        AdminMail::alert(new AdminAlert(
            "New booking {$booking->reference}",
            [
                "{$booking->customer_name} booked {$booking->tour?->title}.",
                "Date: {$booking->tour_date->format('d M Y')} | Guests: {$booking->adults} adult(s), {$booking->children} child(ren)",
                "Total: {$booking->currency} ".number_format((float) $booking->grand_total),
                "Phone: {$booking->phone} | Email: {$booking->email}",
            ],
            'Open booking',
            route('admin.bookings.show', $booking),
        ));
    }
}