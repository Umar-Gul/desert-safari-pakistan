<?php

declare(strict_types=1);

namespace App\Support;

use App\Notifications\AdminAlert;
use Illuminate\Support\Facades\Notification;

final class AdminMail
{
    /** admin_notification_email, then the contact email, then the mail "from" address. */
    public static function recipient(): ?string
    {
        foreach ([setting('admin_notification_email'), setting('email'), config('mail.from.address')] as $email) {
            if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    public static function alert(AdminAlert $alert): void
    {
        $to = self::recipient();

        if ($to !== null) {
            Notification::route('mail', $to)->notify($alert);
        }
    }
}