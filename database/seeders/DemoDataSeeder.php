<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\CacheKeys;
use Illuminate\Database\Seeder;

/** Realistic Pakistan demo catalogue. Safe to run repeatedly. */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogueSeeder::class,
            TourSeeder::class,
            ContentSeeder::class,
            AvailabilitySeeder::class,
        ]);

        Setting::flushCache();
        CacheKeys::forgetCatalogue();
    }
}