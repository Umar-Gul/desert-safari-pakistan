<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\Tour;
use App\Support\CacheKeys;

/** Keeps tours.rating_cached and tours.reviews_count_cached in sync. */
class ReviewObserver
{
    public function saved(Review $review): void
    {
        $this->refresh((int) $review->tour_id);

        // Review moved to another tour: refresh the old one too.
        if ($review->wasChanged('tour_id')) {
            $this->refresh((int) $review->getOriginal('tour_id'));
        }
    }

    public function deleted(Review $review): void
    {
        $this->refresh((int) $review->tour_id);
    }

    public function restored(Review $review): void
    {
        $this->refresh((int) $review->tour_id);
    }

    public function forceDeleted(Review $review): void
    {
        $this->refresh((int) $review->tour_id);
    }

    private function refresh(int $tourId): void
    {
        if ($tourId <= 0) {
            return;
        }

        $stats = Review::query()
            ->where('tour_id', $tourId)
            ->where('status', ReviewStatus::Approved->value)
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')
            ->first();

        // Query-builder update: no model events, so no loops.
        Tour::withTrashed()->whereKey($tourId)->update([
            'rating_cached' => round((float) $stats->average, 2),
            'reviews_count_cached' => (int) $stats->total,
        ]);

        CacheKeys::forgetCatalogue();
    }
}