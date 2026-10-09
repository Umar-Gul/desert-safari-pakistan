<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Opens the next 90 days for every tour. Weekends cost 10% more on featured tours. */
class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        foreach (Tour::query()->get(['id', 'base_price_adult', 'max_group_size', 'is_featured']) as $tour) {
            $capacity = $tour->max_group_size ? min(40, (int) $tour->max_group_size * 2) : 40;
            $weekendPrice = $tour->is_featured ? round((float) $tour->base_price_adult * 1.10, -2) : null;

            for ($i = 0; $i < 90; $i++) {
                $date = today()->addDays($i);

                $rows[] = [
                    'tour_id' => $tour->id,
                    'date' => $date->toDateString(),
                    'capacity' => $capacity,
                    'booked_count' => 0,
                    'is_closed' => false,
                    'price_override' => $date->isWeekend() ? $weekendPrice : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // The unique (tour_id, date) index makes this safe to run again.
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('availabilities')->insertOrIgnore($chunk);
        }
    }
}