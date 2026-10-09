<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AvailabilityBulkRequest;
use App\Models\Availability;
use App\Models\Tour;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Availability::class);

        $tours = Tour::query()->orderBy('title')->get(['id', 'title']);
        $tour = $tours->firstWhere('id', (int) $request->input('tour_id')) ?? $tours->first();
        $month = $this->month((string) $request->input('month'));

        $days = [];

        if ($tour) {
            $rows = Availability::query()
                ->where('tour_id', $tour->id)
                ->whereDate('date', '>=', $month->toDateString())
                ->whereDate('date', '<=', $month->copy()->endOfMonth()->toDateString())
                ->get()
                ->keyBy(fn (Availability $a) => $a->date->toDateString());

            for ($d = $month->copy(); $d->lte($month->copy()->endOfMonth()); $d->addDay()) {
                $row = $rows->get($d->toDateString());

                $days[] = [
                    'date' => $d->toDateString(),
                    'day' => $d->day,
                    'exists' => (bool) $row,
                    'capacity' => $row?->capacity,
                    'booked' => $row?->booked_count,
                    'closed' => (bool) $row?->is_closed,
                    'price' => $row?->price_override,
                ];
            }
        }

        return view('admin.availabilities.index', [
            'tours' => $tours,
            'tour' => $tour,
            'month' => $month,
            'days' => $days,
            'offset' => $month->dayOfWeekIso - 1, // Monday first
            'canEdit' => $request->user()->can('create', Availability::class),
        ]);
    }

    public function bulk(AvailabilityBulkRequest $request): RedirectResponse
    {
        Gate::authorize('create', Availability::class);

        $v = $request->validated();
        $tourId = (int) $v['tour_id'];
        $from = Carbon::parse($v['from'])->startOfDay();
        $to = Carbon::parse($v['to'])->startOfDay();
        $weekdays = array_map('intval', $v['weekdays'] ?? []);

        $existing = Availability::query()
            ->where('tour_id', $tourId)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get()
            ->keyBy(fn (Availability $a) => $a->date->toDateString());

        $changed = 0;
        $skipped = 0;

        DB::transaction(function () use ($v, $tourId, $from, $to, $weekdays, $existing, $request, &$changed, &$skipped) {
            foreach (CarbonPeriod::create($from, $to) as $day) {
                if ($weekdays !== [] && ! in_array($day->dayOfWeek, $weekdays, true)) {
                    continue;
                }

                $key = $day->toDateString();
                $row = $existing->get($key);

                switch ($v['action']) {
                    case 'set':
                        $capacity = max((int) $v['capacity'], (int) ($row?->booked_count ?? 0));
                        $price = filled($v['price_override'] ?? null)
                            ? $v['price_override']
                            : ($request->boolean('clear_price') ? null : $row?->price_override);

                        if ($row) {
                            $row->update(['capacity' => $capacity, 'is_closed' => false, 'price_override' => $price]);
                        } else {
                            Availability::create([
                                'tour_id' => $tourId,
                                'date' => $key,
                                'capacity' => $capacity,
                                'booked_count' => 0,
                                'is_closed' => false,
                                'price_override' => $price,
                            ]);
                        }
                        $changed++;
                        break;

                    case 'close':
                    case 'open':
                        if ($row) {
                            $row->update(['is_closed' => $v['action'] === 'close']);
                            $changed++;
                        }
                        break;

                    case 'delete':
                        if ($row && (int) $row->booked_count === 0) {
                            $row->delete();
                            $changed++;
                        } elseif ($row) {
                            $skipped++;
                        }
                        break;
                }
            }
        });

        $message = "{$changed} date(s) updated.".($skipped ? " {$skipped} date(s) with bookings were kept." : '');

        return redirect()
            ->route('admin.availabilities.index', ['tour_id' => $tourId, 'month' => $from->format('Y-m')])
            ->with('success', $message);
    }

    private function month(string $value): Carbon
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) === 1) {
            return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfDay();
        }

        return now()->startOfMonth();
    }
}