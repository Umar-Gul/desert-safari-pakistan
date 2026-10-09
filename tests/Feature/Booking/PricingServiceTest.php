<?php

declare(strict_types=1);

namespace Tests\Feature\Booking;

use App\Exceptions\BookingException;
use App\Models\Addon;
use App\Models\CampType;
use App\Models\Coupon;
use App\Models\Vehicle;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBookingConfig();
        $this->pricing = app(PricingService::class);
    }

    public function test_tour_discount_applies_to_base_prices(): void
    {
        $tour = $this->makeTour();
        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour)));

        // 2 x 10,000 + 1 x 6,000 = 26,000; 10% off = 2,600.
        $this->assertEquals(26000, $price->peopleGross);
        $this->assertEquals(2600, $price->tourDiscount);
        $this->assertEquals(26000, $price->subtotal);
        $this->assertEquals(2600, $price->discountTotal);
        $this->assertEquals(23400, $price->grandTotal);
    }

    public function test_fixed_camp_modifier_is_charged_per_guest(): void
    {
        $tour = $this->makeTour();
        $camp = CampType::factory()->create(['price_modifier_value' => 1000]);
        $tour->campTypes()->attach($camp);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['camp_type_id' => $camp->id]));

        $this->assertEquals(3000, $price->campAmount);
        $this->assertEquals(29000, $price->subtotal);
        $this->assertEquals(26400, $price->grandTotal);
    }

    public function test_percent_camp_modifier_uses_discounted_people_total(): void
    {
        $tour = $this->makeTour();
        $camp = CampType::factory()->percent(10)->create();
        $tour->campTypes()->attach($camp);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['camp_type_id' => $camp->id]));

        $this->assertEquals(2340, $price->campAmount);
        $this->assertEquals(25740, $price->grandTotal);
    }

    public function test_vehicle_modifier_is_charged_once(): void
    {
        $tour = $this->makeTour();
        $vehicle = Vehicle::factory()->create(['price_modifier' => 2000]);
        $tour->vehicles()->attach($vehicle);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['vehicle_id' => $vehicle->id]));

        $this->assertEquals(2000, $price->vehicleAmount);
        $this->assertEquals(25400, $price->grandTotal);
    }

    public function test_addon_uses_tour_price_override_and_quantity(): void
    {
        $tour = $this->makeTour();
        $addon = Addon::factory()->create(['price' => 500]);
        $tour->addons()->attach($addon->id, ['price_override' => 800]);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['addons' => [$addon->id => 2]]));

        $this->assertCount(1, $price->addonLines);
        $this->assertEquals(800, $price->addonLines[0]['unit_price']);
        $this->assertEquals(1600, $price->addonsTotal);
        $this->assertEquals(25000, $price->grandTotal);
    }

    public function test_date_price_override_replaces_adult_price_and_scales_child_price(): void
    {
        $tour = $this->makeTour();
        $availability = $this->openDate($tour, 5, 10, ['price_override' => 12000]);

        $price = $this->pricing->calculate($tour, $this->quoteInput($availability, ['adults' => 1, 'children' => 1]));

        // Adult 12,000; child 6,000 x 12,000 / 10,000 = 7,200.
        $this->assertEquals(19200, $price->peopleGross);
        $this->assertEquals(17280, $price->grandTotal);
    }

    public function test_percent_coupon_applies_after_tour_discount(): void
    {
        $tour = $this->makeTour();
        Coupon::factory()->create(['code' => 'SAVE10']);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['coupon_code' => 'save10']));

        $this->assertNull($price->couponError);
        $this->assertEquals('SAVE10', $price->couponCode);
        $this->assertEquals(2340, $price->couponDiscount);
        $this->assertEquals(4940, $price->discountTotal);
        $this->assertEquals(21060, $price->grandTotal);
    }

    public function test_fixed_coupon_subtracts_the_amount(): void
    {
        $tour = $this->makeTour();
        Coupon::factory()->fixed(1000)->create(['code' => 'FLAT1000']);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['coupon_code' => 'FLAT1000']));

        $this->assertEquals(22400, $price->grandTotal);
    }

    public function test_coupon_below_minimum_amount_is_reported_not_applied(): void
    {
        $tour = $this->makeTour();
        Coupon::factory()->create(['code' => 'BIGONLY', 'min_amount' => 50000]);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['coupon_code' => 'BIGONLY']));

        $this->assertNotNull($price->couponError);
        $this->assertEquals(0, $price->couponDiscount);
        $this->assertEquals(23400, $price->grandTotal);
    }

    public function test_expired_coupon_is_reported_not_applied(): void
    {
        $tour = $this->makeTour();
        Coupon::factory()->expired()->create(['code' => 'OLD']);

        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['coupon_code' => 'OLD']));

        $this->assertNotNull($price->couponError);
        $this->assertEquals(23400, $price->grandTotal);
    }

    public function test_tax_is_added_on_top(): void
    {
        config(['booking.tax_percent' => 10]);

        $tour = $this->makeTour();
        $price = $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour)));

        $this->assertEquals(2340, $price->tax);
        $this->assertEquals(25740, $price->grandTotal);
    }

    public function test_vehicle_not_attached_to_tour_is_rejected(): void
    {
        $tour = $this->makeTour();
        $vehicle = Vehicle::factory()->create();

        $this->expectException(BookingException::class);

        $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour), ['vehicle_id' => $vehicle->id]));
    }

    public function test_group_larger_than_tour_maximum_is_rejected(): void
    {
        $tour = $this->makeTour(['max_group_size' => 2]);

        $this->expectException(BookingException::class);

        $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour)));
    }

    public function test_inactive_tour_is_rejected(): void
    {
        $tour = $this->makeTour(['is_active' => false]);

        $this->expectException(BookingException::class);

        $this->pricing->calculate($tour, $this->quoteInput($this->openDate($tour)));
    }
}