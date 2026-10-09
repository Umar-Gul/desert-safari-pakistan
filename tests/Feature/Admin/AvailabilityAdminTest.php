<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Availability;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsStaff;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class AvailabilityAdminTest extends TestCase
{
    use ActsAsStaff, CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_bulk_set_creates_open_dates_for_the_chosen_weekdays_only(): void
    {
        $tour = $this->makeTour();
        $from = now()->addDay()->startOfDay();
        $to = $from->copy()->addDays(13);

        $weekendDays = collect(CarbonPeriod::create($from, $to))
            ->filter(fn ($d) => in_array($d->dayOfWeek, [0, 6], true))
            ->count();

        $this->actingAs($this->staff('admin'))->post('/admin/availabilities', [
            'tour_id' => $tour->id,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'action' => 'set',
            'capacity' => 20,
            'price_override' => 12000,
            'weekdays' => [0, 6],
        ])->assertSessionHasNoErrors();

        $rows = Availability::where('tour_id', $tour->id)->get();

        $this->assertCount($weekendDays, $rows);
        $this->assertTrue($rows->every(fn ($r) => $r->capacity === 20 && ! $r->is_closed && (float) $r->price_override === 12000.0));
    }

    public function test_capacity_never_drops_below_guests_already_booked(): void
    {
        $tour = $this->makeTour();
        $row = $this->openDate($tour, 5, 10, ['booked_count' => 8]);

        $this->actingAs($this->staff('admin'))->post('/admin/availabilities', [
            'tour_id' => $tour->id,
            'from' => $row->date->toDateString(),
            'to' => $row->date->toDateString(),
            'action' => 'set',
            'capacity' => 5,
        ])->assertSessionHasNoErrors();

        $this->assertSame(8, $row->fresh()->capacity);
    }

    public function test_close_and_reopen(): void
    {
        $tour = $this->makeTour();
        $row = $this->openDate($tour);
        $admin = $this->staff('admin');
        $range = ['tour_id' => $tour->id, 'from' => $row->date->toDateString(), 'to' => $row->date->toDateString()];

        $this->actingAs($admin)->post('/admin/availabilities', $range + ['action' => 'close']);
        $this->assertTrue($row->fresh()->is_closed);

        $this->actingAs($admin)->post('/admin/availabilities', $range + ['action' => 'open']);
        $this->assertFalse($row->fresh()->is_closed);
    }

    public function test_remove_skips_dates_that_have_bookings(): void
    {
        $tour = $this->makeTour();
        $empty = $this->openDate($tour, 5, 10);
        $booked = $this->openDate($tour, 6, 10, ['booked_count' => 2]);

        $this->actingAs($this->staff('admin'))->post('/admin/availabilities', [
            'tour_id' => $tour->id,
            'from' => $empty->date->toDateString(),
            'to' => $booked->date->toDateString(),
            'action' => 'delete',
        ])->assertSessionHasNoErrors();

        $this->assertNull(Availability::find($empty->id));
        $this->assertNotNull(Availability::find($booked->id));
    }

    public function test_calendar_renders_and_support_is_forbidden(): void
    {
        $tour = $this->makeTour();
        $this->openDate($tour);

        $this->actingAs($this->staff('admin'))
            ->get('/admin/availabilities?tour_id='.$tour->id.'&month='.now()->format('Y-m'))
            ->assertOk()
            ->assertSee('Update dates');

        $this->actingAs($this->staff('support'))->get('/admin/availabilities')->assertForbidden();
    }
}