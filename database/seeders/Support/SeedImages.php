<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Throwable;

/** Draws desert-coloured placeholder images with GD so the demo site looks populated. */
final class SeedImages
{
    public static function attach(HasMedia $model, string $key, string $alt = '', string $collection = 'cover'): void
    {
        if (! config('site.seed_images', true) || ! function_exists('imagecreatetruecolor')) {
            return;
        }

        try {
            if ($model->media()->where('collection_name', $collection)->exists()) {
                return;
            }

            $model->addMedia(self::file($key))
                ->preservingOriginal()
                ->usingFileName(Str::slug($key).'.png')
                ->withCustomProperties(['alt' => $alt])
                ->toMediaCollection($collection);
        } catch (Throwable $e) {
            report($e); // never let an image problem stop the seeding
        }
    }

    private static function file(string $key): string
    {
        $dir = storage_path('app/seed-images');
        File::ensureDirectoryExists($dir);

        $path = $dir.'/'.md5($key).'.png';

        if (is_file($path)) {
            return $path;
        }

        $w = 1200;
        $h = 800;
        $img = imagecreatetruecolor($w, $h);
        $hue = 18 + (crc32($key) % 36);

        [$r1, $g1, $b1] = self::hsl($hue + 10, 0.70, 0.80);
        [$r2, $g2, $b2] = self::hsl($hue, 0.65, 0.52);

        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $color = imagecolorallocate(
                $img,
                (int) ($r1 + ($r2 - $r1) * $t),
                (int) ($g1 + ($g2 - $g1) * $t),
                (int) ($b1 + ($b2 - $b1) * $t),
            );
            imageline($img, 0, $y, $w, $y, $color);
        }

        imagefilledellipse($img, (int) ($w * 0.72), (int) ($h * 0.3), 170, 170, imagecolorallocate($img, 255, 240, 200));

        [$dr, $dg, $db] = self::hsl($hue - 6, 0.6, 0.38);
        imagefilledellipse($img, (int) ($w * 0.25), $h + 90, 1200, 520, imagecolorallocate($img, $dr, $dg, $db));

        [$er, $eg, $eb] = self::hsl($hue - 10, 0.55, 0.30);
        imagefilledellipse($img, (int) ($w * 0.8), $h + 140, 1300, 520, imagecolorallocate($img, $er, $eg, $eb));

        imagepng($img, $path);

        return $path;
    }

    /** @return array{0:int,1:int,2:int} */
    private static function hsl(float $h, float $s, float $l): array
    {
        $h = fmod(fmod($h, 360) + 360, 360);
        $c = (1 - abs(2 * $l - 1)) * $s;
        $hp = $h / 60;
        $x = $c * (1 - abs(fmod($hp, 2) - 1));

        [$r, $g, $b] = match (true) {
            $hp < 1 => [$c, $x, 0],
            $hp < 2 => [$x, $c, 0],
            $hp < 3 => [0, $c, $x],
            $hp < 4 => [0, $x, $c],
            $hp < 5 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        $m = $l - $c / 2;

        return [(int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255)];
    }
}