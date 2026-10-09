<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasListingScopes;
use App\Models\Concerns\HasSlug;
use App\Observers\CatalogueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(CatalogueObserver::class)]
class BlogCategory extends Model
{
    use HasFactory, HasListingScopes, HasSlug;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }
}