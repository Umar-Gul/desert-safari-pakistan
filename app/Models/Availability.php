<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'capacity' => 'integer',
            'booked_count' => 'integer',
            'is_closed' => 'boolean',
            'price_override' => 'decimal:2',
        ];
    }

    protected function remaining(): Attribute
    {
        return Attribute::get(fn () => max(0, (int) $this->capacity - (int) $this->booked_count));
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_closed', false);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}