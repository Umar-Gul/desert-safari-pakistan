<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdjustmentType;
use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasListingScopes;
use App\Models\Concerns\HasSlug;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

#[ObservedBy(CatalogueObserver::class)]
class CampType extends Model implements HasMedia
{
    use HasCoverImage, HasFactory, HasListingScopes, HasSlug, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price_modifier_type' => AdjustmentType::class,
            'price_modifier_value' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Amount added to the given base (fixed amount, or percent of the base). */
    public function modifierAmount(float $base): float
    {
        $value = (float) $this->price_modifier_value;

        return round(
            $this->price_modifier_type === AdjustmentType::Percent ? $base * $value / 100 : $value,
            2
        );
    }

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class);
    }
}