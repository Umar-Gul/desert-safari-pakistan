<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Enums\TourBadge;
use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasListingScopes;
use App\Models\Concerns\HasSlug;
use App\Observers\TourObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

#[ObservedBy(TourObserver::class)]
class Tour extends Model implements HasMedia
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
            'duration_hours' => 'decimal:1',
            'base_price_adult' => 'decimal:2',
            'base_price_child' => 'decimal:2',
            'discount_percent' => 'integer',
            'rating_cached' => 'decimal:2',
            'reviews_count_cached' => 'integer',
            'has_dinner' => 'boolean',
            'has_breakfast' => 'boolean',
            'has_guide' => 'boolean',
            'has_transfer' => 'boolean',
            'has_itinerary' => 'boolean',
            'max_group_size' => 'integer',
            'min_age' => 'integer',
            'badges' => AsEnumCollection::of(TourBadge::class),
            'is_featured' => 'boolean',
            'is_best_selling' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Extra "gallery" collection (multiple images) on top of "cover". */
    public function registerExtraMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // ---- Computed prices (never stored) ----

    protected function finalPriceAdult(): Attribute
    {
        return Attribute::get(fn () => $this->applyDiscount((float) $this->base_price_adult));
    }

    protected function finalPriceChild(): Attribute
    {
        return Attribute::get(fn () => $this->applyDiscount((float) $this->base_price_child));
    }

    /** Saving per adult. */
    protected function discountAmount(): Attribute
    {
        return Attribute::get(
            fn () => round((float) $this->base_price_adult - $this->applyDiscount((float) $this->base_price_adult), 2)
        );
    }

    protected function hasDiscount(): Attribute
    {
        return Attribute::get(fn () => (int) $this->discount_percent > 0);
    }

    private function applyDiscount(float $amount): float
    {
        $percent = max(0, min(100, (int) $this->discount_percent));

        return round($amount * (100 - $percent) / 100, 2);
    }

    // ---- Scopes ----

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_featured'), true);
    }

    public function scopeBestSelling(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_best_selling'), true);
    }

    /** Relations needed to render a tour card without N+1 queries. */
    public function scopeWithCardRelations(Builder $query): Builder
    {
        return $query->with(['category:id,name,slug', 'desert:id,name,slug', 'media']);
    }

    // ---- Relations ----

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function desert(): BelongsTo
    {
        return $this->belongsTo(Desert::class);
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(TourHighlight::class)->orderBy('sort_order');
    }

    public function itineraryItems(): HasMany
    {
        return $this->hasMany(TourItineraryItem::class)->orderBy('sort_order');
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(TourInclusion::class)->orderBy('sort_order');
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(TourExclusion::class)->orderBy('sort_order');
    }

    public function additionalInfos(): HasMany
    {
        return $this->hasMany(TourAdditionalInfo::class)->orderBy('sort_order');
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class)->withPivot('price_override');
    }

    public function campTypes(): BelongsToMany
    {
        return $this->belongsToMany(CampType::class);
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)
            ->where('status', ReviewStatus::Approved->value)
            ->latest();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function approvedQuestions(): HasMany
    {
        return $this->hasMany(Question::class)
            ->where('status', ReviewStatus::Approved->value)
            ->latest();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}