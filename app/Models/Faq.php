<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FaqScope;
use App\Models\Concerns\HasListingScopes;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(CatalogueObserver::class)]
class Faq extends Model
{
    use HasFactory, HasListingScopes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'scope' => FaqScope::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeGlobalOnly(Builder $query): Builder
    {
        return $query->where('scope', FaqScope::Global->value);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}