<?php

declare(strict_types=1);

namespace App\Observers;

use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Model;

/** Generic cache flusher for any admin-managed public content. */
class CatalogueObserver
{
    public function saved(Model $model): void
    {
        CacheKeys::forgetCatalogue();
    }

    public function deleted(Model $model): void
    {
        CacheKeys::forgetCatalogue();
    }

    public function restored(Model $model): void
    {
        CacheKeys::forgetCatalogue();
    }

    public function forceDeleted(Model $model): void
    {
        CacheKeys::forgetCatalogue();
    }
}