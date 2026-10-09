<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Category;
use App\Support\CacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Throwable;

/** Category links for the header and footer. Cached; cleared by the catalogue observers. */
class NavigationComposer
{
    public function compose(View $view): void
    {
        try {
            $categories = Cache::remember(CacheKeys::NAV_CATEGORIES, 600, function () {
                $flagged = Category::query()->active()
                    ->where(fn ($q) => $q->where('show_in_menu', true)->orWhere('is_quick_link', true))
                    ->ordered()
                    ->get(['id', 'name', 'slug']);

                return $flagged->isNotEmpty()
                    ? $flagged
                    : Category::query()->active()->ordered()->limit(6)->get(['id', 'name', 'slug']);
            });
        } catch (Throwable) {
            $categories = new Collection();
        }

        $view->with('navCategories', $categories);
    }
}