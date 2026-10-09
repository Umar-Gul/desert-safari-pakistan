<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Explicit cache keys (no cache tags, so it works with file/database drivers).
 * Observers call forgetCatalogue(); Phase 5/6 read through these keys.
 */
final class CacheKeys
{
    public const HOME = 'home.sections';

    public const NAV_CATEGORIES = 'nav.categories';

    public const TOUR_LIST = 'tours.default_list';

    public const BLOG_LATEST = 'blog.latest';

    public const SITEMAP = 'sitemap.xml';

    public static function forgetCatalogue(): void
    {
        foreach ([self::HOME, self::NAV_CATEGORIES, self::TOUR_LIST, self::BLOG_LATEST, self::SITEMAP] as $key) {
            Cache::forget($key);
        }
    }
}