<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Auto-generates a unique slug when empty and binds routes by slug. */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function (Model $model) {
            if (blank($model->slug)) {
                $model->slug = $model->makeUniqueSlug((string) $model->{$model->slugSourceColumn()});
            }
        });
    }

    public function slugSourceColumn(): string
    {
        return 'name';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function makeUniqueSlug(string $source): string
    {
        $base = Str::slug($source) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        // Includes soft-deleted rows because the unique index covers them.
        while (static::withoutGlobalScopes()
            ->where('slug', $slug)
            ->where($this->getKeyName(), '!=', $this->getKey() ?? 0)
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}