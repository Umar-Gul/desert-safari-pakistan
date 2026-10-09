<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasListingScopes;
use App\Models\Concerns\HasSlug;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

#[ObservedBy(CatalogueObserver::class)]
class Category extends Model implements HasMedia
{
    use HasCoverImage, HasFactory, HasListingScopes, HasSlug, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'is_quick_link' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Every tour shown in this category (includes tours whose primary category it is). */
    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class);
    }

    public function primaryTours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }

    /** Adds `activities_count` (active tours only). Computed, never stored. */
    public function scopeWithActivitiesCount(Builder $query): Builder
    {
        return $query->withCount([
            'tours as activities_count' => fn ($q) => $q->where('tours.is_active', true),
        ]);
    }

    /** Header dropdown list. */
    public function scopeInMenu(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('show_in_menu'), true);
    }

    /** The few links shown directly in the top bar. */
    public function scopeQuickLinks(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_quick_link'), true);
    }
}