<?php

declare(strict_types=1);

namespace App\Mail;

use App\DataTransferObjects\CancellationQuote;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class BookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public CancellationQuote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your booking '.$this->booking->reference.' was cancelled');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.booking.cancelled', with: [
            'booking' => $this->booking,
            'quote' => $this->quote,
            'firstName' => Str::before($this->booking->customer_name, ' '),
            'whatsappUrl' => whatsapp_url('Hello, my booking reference is '.$this->booking->reference),
        ]);
    }
}