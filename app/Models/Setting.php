<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

#[ObservedBy(SettingObserver::class)]
class Setting extends Model
{
    public const CACHE_KEY = 'settings.all';

    protected $fillable = ['group', 'key', 'value', 'type'];

    public function castValue(): mixed
    {
        return match ($this->type) {
            'int' => (int) $this->value,
            'bool' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => $this->value === null ? [] : (json_decode($this->value, true) ?? []),
            default => $this->value,
        };
    }

    /**
     * All settings as key => typed value, merged over config defaults.
     * Cached forever; flushed by SettingObserver.
     *
     * @return array<string, mixed>
     */
    public static function map(): array
    {
        $stored = [];

        try {
            $stored = Cache::rememberForever(
                self::CACHE_KEY,
                fn () => static::query()->get()->mapWithKeys(
                    fn (self $s) => [$s->key => $s->castValue()]
                )->all()
            );
        } catch (Throwable) {
            // DB/cache unavailable (fresh install, error page): use defaults only.
        }

        return array_merge(config('site.defaults', []), $stored);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $map = static::map();

        return array_key_exists($key, $map) && $map[$key] !== null && $map[$key] !== ''
            ? $map[$key]
            : $default;
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}