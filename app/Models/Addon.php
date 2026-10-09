<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasListingScopes;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

#[ObservedBy(CatalogueObserver::class)]
class Addon extends Model implements HasMedia
{
    use HasCoverImage, HasFactory, HasListingScopes, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Price for the current tour context: pivot override if loaded via a tour, else the base price. */
    protected function effectivePrice(): Attribute
    {
        return Attribute::get(function () {
            $override = $this->relationLoaded('pivot') ? $this->pivot->price_override ?? null : null;

            return (float) ($override ?? $this->price);
        });
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class)->withPivot('price_override');
    }
}