<?php

declare(strict_types=1);

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Read a site setting (cached). Falls back to config('site.defaults').
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('whatsapp_url')) {
    /**
     * Working wa.me link built from the settings number.
     */
    function whatsapp_url(?string $message = null): string
    {
        $number = preg_replace('/\D+/', '', (string) setting('whatsapp_number', ''));

        if ($number === '' || $number === null) {
            return '#';
        }

        $url = 'https://wa.me/'.$number;

        return $message ? $url.'?text='.rawurlencode($message) : $url;
    }
}

if (! function_exists('phone_href')) {
    function phone_href(?string $phone = null): string
    {
        $phone ??= (string) setting('phone', '');

        return 'tel:'.preg_replace('/[^\d+]/', '', $phone);
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount with the configured currency, e.g. "Rs 12,500".
     */
    function money(float|int|string|null $amount, ?string $symbol = null): string
    {
        $symbol ??= (string) setting('currency_symbol', 'Rs');

        return $symbol.' '.number_format((float) $amount, 0);
    }
}