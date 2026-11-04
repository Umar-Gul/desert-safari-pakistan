<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Availability;
use App\Support\CacheKeys;

class AvailabilityObserver
{
    public function created(Availability $availability): void
    {
        CacheKeys::forgetCatalogue();
    }

    public function updated(Availability $availability): void
    {
        CacheKeys::forgetCatalogue();
    }

    public function deleted(Availability $availability): void
    {
        CacheKeys::forgetCatalogue();
    }
}
