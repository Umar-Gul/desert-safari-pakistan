<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * "cover" collection (single image) with WebP conversions.
 * A model may define registerExtraMediaCollections() for more collections.
 * The model itself must implement Spatie\MediaLibrary\HasMedia.
 */
trait HasCoverImage
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        if (method_exists($this, 'registerExtraMediaCollections')) {
            $this->registerExtraMediaCollections();
        }
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->fit(Fit::Crop, 400, 300)
            ->nonQueued();

        $this->addMediaConversion('card')
            ->format('webp')
            ->fit(Fit::Crop, 800, 600)
            ->withResponsiveImages()
            ->nonQueued();

        $this->addMediaConversion('large')
            ->format('webp')
            ->fit(Fit::Max, 1600, 1600)
            ->withResponsiveImages()
            ->nonQueued();
    }

    /** Eager-load "media" before calling this in lists (lazy loading is blocked in dev). */
    public function coverUrl(string $conversion = 'card'): ?string
    {
        $url = $this->getFirstMediaUrl('cover', $conversion);

        return $url !== '' ? $url : null;
    }

    public function coverAlt(): string
    {
        $alt = $this->getFirstMedia('cover')?->getCustomProperty('alt');

        return (string) ($alt ?: ($this->title ?? $this->name ?? ''));
    }
}