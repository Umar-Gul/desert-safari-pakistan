<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Mail\BookingConfirmedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmedNotification implements ShouldQueue
{
    public int $tries = 3;

    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking->load(['tour', 'campType', 'vehicle', 'addons', 'payments']);

        Mail::to($booking->email, $booking->customer_name)->send(new BookingConfirmedMail($booking));
    }
}