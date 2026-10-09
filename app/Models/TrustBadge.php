<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasListingScopes;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

/** The badges under the hero ("Top Rated Company", "24/7 Customer Support"...). Icon = cover image. */
#[ObservedBy(CatalogueObserver::class)]
class TrustBadge extends Model implements HasMedia
{
    use HasCoverImage, HasFactory, HasListingScopes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}