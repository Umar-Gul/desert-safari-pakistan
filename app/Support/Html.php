<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class Html
{
    /** Purified HTML for editor content. Cached by content hash. */
    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        return Cache::remember('html.'.md5($html), 3600, fn () => function_exists('clean')
            ? (string) clean($html)
            : strip_tags($html, '<p><br><h2><h3><h4><ul><ol><li><strong><em><u><a><blockquote><img>'));
    }
}