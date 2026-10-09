<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GalleryItemType;
use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasListingScopes;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

#[ObservedBy(CatalogueObserver::class)]
class GalleryItem extends Model implements HasMedia
{
    use HasCoverImage, HasFactory, HasListingScopes, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => GalleryItemType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}