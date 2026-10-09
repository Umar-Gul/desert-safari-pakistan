<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class QuoteApiTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBookingConfig();
    }

    public function test_quote_returns_the_server_calculated_price(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $response = $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $date->date->toDateString(),
            'adults' => 2,
            'children' => 1,
        ])->assertOk();

        // 2 x 10,000 + 1 x 6,000 = 26,000, minus 10% tour discount.
        $this->assertEquals(23400, $response->json('price.grand_total'));
        $this->assertEquals(2600, $response->json('price.tour_discount'));
        $this->assertEquals(10, $response->json('remaining'));
    }

    public function test_invalid_coupon_is_reported_but_the_quote_still_works(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);

        $response = $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $date->date->toDateString(),
            'adults' => 2,
            'children' => 1,
            'coupon_code' => 'NOPE',
        ])->assertOk();

        $this->assertNotNull($response->json('price.coupon_error'));
        $this->assertEquals(23400, $response->json('price.grand_total'));
    }

    public function test_valid_coupon_lowers_the_total(): void
    {
        $tour = $this->makeTour();
        $date = $this->openDate($tour);
        Coupon::factory()->create(['code' => 'SAVE10']);

        $response = $this->postJson(route('tours.price', $tour->slug), [
            'tour_date' => $date->date->toDateString(),
            'adults' => 2,
            'children' => 1,
            'coupon_code' => 'save10',
        ])->assertOk();

        $this->assertEquals(21060, $response->json('price.grand_total'));
    }

    public function test_closed_or_full_dates_return_a_readable_error(): void
    {
        $tour = $this->makeTour();
        $closed = $this->openDate($tour, 4, 10, ['is_closed' => true]);
        $small = $this->openDate($tour, 5, 2);

        $this->postJson(route('tours.price', $tour->slug), ['tour_date' => $closed->date->toDateString(), 'adults' => 1])
            ->assertStatus(422)->assertJsonStructure(['message']);

        $this->postJson(route('tours.price', $tour->slug), ['tour_date' => $small->date->toDateString(), 'adults' => 3])
            ->assertStatus(422)->assertJsonPath('message', 'Only 2 spots are left on this date.');
    }

    public function test_invalid_input_and_unknown_tours_are_rejected(): void
    {
        $tour = $this->makeTour();

        $this->postJson(route('tours.price', $tour->slug), ['adults' => 0])->assertStatus(422)->assertJsonValidationErrors(['tour_date', 'adults']);
        $this->postJson('/tours/missing/price', ['tour_date' => now()->addDay()->toDateString(), 'adults' => 1])->assertNotFound();
    }
}