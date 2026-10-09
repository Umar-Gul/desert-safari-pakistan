<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\PaymentMethod;
use App\Models\Booking;
use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your booking '.$this->booking->reference.' is confirmed');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.booking.confirmed', with: [
            'booking' => $this->booking,
            'firstName' => Str::before($this->booking->customer_name, ' '),
            'trackUrl' => route('track.form'),
            'whatsappUrl' => whatsapp_url('Hello, my booking reference is '.$this->booking->reference),
            'methods' => collect((array) setting('payment_methods', []))
                ->map(fn ($value) => PaymentMethod::tryFrom((string) $value)?->label())
                ->filter()->values()->all(),
            'bank' => trim((string) setting('bank_details', '')),
        ]);
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        $vouchers = app(VoucherService::class);

        return [
            Attachment::fromData(fn () => $vouchers->pdf($this->booking), $vouchers->filename($this->booking))
                ->withMime('application/pdf'),
        ];
    }
}