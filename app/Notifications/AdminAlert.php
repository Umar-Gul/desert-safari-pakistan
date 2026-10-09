<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** One generic staff alert (new booking, enquiry, review, question...). */
class AdminAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param list<string> $lines */
    public function __construct(
        public string $heading,
        public array $lines,
        public string $actionText,
        public string $actionUrl,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->subject($this->heading)->greeting($this->heading);

        foreach ($this->lines as $line) {
            $message->line($line);
        }

        return $message->action($this->actionText, $this->actionUrl);
    }
}