<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Mail\BookingCancelledMail;
use App\Notifications\AdminAlert;
use App\Support\AdminMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingCancelledNotifications implements ShouldQueue
{
    public int $tries = 3;

    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking->load(['tour', 'campType', 'vehicle', 'addons', 'payments']);

        Mail::to($booking->email, $booking->customer_name)->send(new BookingCancelledMail($booking, $event->quote));

        AdminMail::alert(new AdminAlert(
            "Booking cancelled: {$booking->reference}",
            [
                "{$booking->customer_name} / {$booking->tour?->title} on {$booking->tour_date->format('d M Y')}.",
                $event->quote->isFree
                    ? 'No cancellation fee applies.'
                    : "Cancellation fee: {$booking->currency} ".number_format($event->quote->feeAmount).'. Check whether a refund is due.',
            ],
            'Open booking',
            route('admin.bookings.show', $booking),
        ));
    }
}