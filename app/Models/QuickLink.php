<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuickLinkGroup;
use App\Models\Concerns\HasListingScopes;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Chip links shown in the home page carousels. */
#[ObservedBy(CatalogueObserver::class)]
class QuickLink extends Model
{
    use HasFactory, HasListingScopes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'section' => QuickLinkGroup::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeInSection(Builder $query, QuickLinkGroup $section): Builder
    {
        return $query->where('section', $section->value);
    }
}