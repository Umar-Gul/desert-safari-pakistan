<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class BookingReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'We received your booking '.$this->booking->reference);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.booking.received', with: [
            'booking' => $this->booking,
            'firstName' => Str::before($this->booking->customer_name, ' '),
            'trackUrl' => route('track.form'),
            'whatsappUrl' => whatsapp_url('Hello, my booking reference is '.$this->booking->reference),
        ]);
    }
}