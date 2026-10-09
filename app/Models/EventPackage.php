<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasListingScopes;
use App\Models\Concerns\HasSlug;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

#[ObservedBy(CatalogueObserver::class)]
class EventPackage extends Model implements HasMedia
{
    use HasCoverImage, HasFactory, HasListingScopes, HasSlug, SoftDeletes;

    protected $guarded = ['id'];

    public function slugSourceColumn(): string
    {
        return 'title';
    }

    protected function casts(): array
    {
        return [
            'adult_price' => 'decimal:2',
            'child_price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}