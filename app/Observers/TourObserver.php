<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tour;
use App\Support\CacheKeys;

class TourObserver
{
    public function saved(Tour $tour): void
    {
        // The primary category is always part of the category_tour pivot.
        if ($tour->category_id) {
            $tour->categories()->syncWithoutDetaching([$tour->category_id]);
        }

        CacheKeys::forgetCatalogue();
    }

    public function deleted(Tour $tour): void
    {
        CacheKeys::forgetCatalogue();
    }

    public function restored(Tour $tour): void
    {
        CacheKeys::forgetCatalogue();
    }

    public function forceDeleted(Tour $tour): void
    {
        CacheKeys::forgetCatalogue();
    }
}