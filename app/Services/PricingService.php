<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\PriceBreakdown;
use App\DataTransferObjects\QuoteInput;
use App\Enums\AdjustmentType;
use App\Exceptions\BookingException;
use App\Models\Availability;
use App\Models\Coupon;
use App\Models\Tour;

/**
 * The only place prices are calculated. Always called server-side;
 * client totals are never trusted.
 */
class PricingService
{
    public function __construct(private readonly BookingRules $rules) {}

    public function calculate(Tour $tour, QuoteInput $input): PriceBreakdown
    {
        if (! $tour->is_active) {
            throw new BookingException('This tour is not available for booking.');
        }

        $this->assertGuests($tour, $input);

        $availability = Availability::query()
            ->where('tour_id', $tour->id)
            ->whereDate('date', $input->tourDate->toDateString())
            ->first();

        [$adultBase, $childBase] = $this->baseUnitPrices($tour, $availability);

        $gross = round($input->adults * $adultBase + $input->children * $childBase, 2);
        $tourDiscount = round($gross * max(0, min(100, (int) $tour->discount_percent)) / 100, 2);
        $peopleNet = round($gross - $tourDiscount, 2);

        $camp = $this->campAmount($tour, $input, $peopleNet);
        $vehicle = $this->vehicleAmount($tour, $input);
        [$addonLines, $addonsTotal] = $this->addonLines($tour, $input);

        $couponBase = round($peopleNet + $camp + $vehicle + $addonsTotal, 2);
        [$coupon, $couponDiscount, $couponError] = $this->coupon($input->couponCode, $couponBase);

        $subtotal = round($gross + $camp + $vehicle, 2);
        $discountTotal = round($tourDiscount + $couponDiscount, 2);
        $taxable = round($subtotal + $addonsTotal - $discountTotal, 2);
        $taxPercent = $this->rules->taxPercent();
        $tax = round($taxable * $taxPercent / 100, 2);

        return new PriceBreakdown(
            currency: $this->rules->currency(),
            adults: $input->adults,
            children: $input->children,
            adultUnitBase: $adultBase,
            childUnitBase: $childBase,
            peopleGross: $gross,
            tourDiscount: $tourDiscount,
            campAmount: $camp,
            vehicleAmount: $vehicle,
            addonLines: $addonLines,
            addonsTotal: $addonsTotal,
            couponId: $coupon?->id,
            couponCode: $coupon?->code,
            couponDiscount: $couponDiscount,
            couponError: $couponError,
            subtotal: $subtotal,
            discountTotal: $discountTotal,
            taxPercent: $taxPercent,
            tax: $tax,
            grandTotal: round($taxable + $tax, 2),
        );
    }

    private function assertGuests(Tour $tour, QuoteInput $input): void
    {
        if ($input->adults < 1) {
            throw new BookingException('At least one adult is required.');
        }

        if ($input->children < 0) {
            throw new BookingException('The number of children is not valid.');
        }

        $guests = $input->guests();

        if ($guests > $this->rules->maxGuests()) {
            throw new BookingException('Please contact us for groups larger than '.$this->rules->maxGuests().'.');
        }

        if ($tour->max_group_size && $guests > (int) $tour->max_group_size) {
            throw new BookingException('This tour allows a maximum of '.$tour->max_group_size.' guests.');
        }
    }

    /**
     * A date-specific price override replaces the adult base price;
     * the child price is scaled by the same ratio.
     *
     * @return array{0: float, 1: float}
     */
    private function baseUnitPrices(Tour $tour, ?Availability $availability): array
    {
        $adult = (float) $tour->base_price_adult;
        $child = (float) $tour->base_price_child;

        if ($availability && $availability->price_override !== null) {
            $override = (float) $availability->price_override;

            if ($adult > 0) {
                $child = round($child * $override / $adult, 2);
            }

            $adult = $override;
        }

        return [$adult, $child];
    }

    private function campAmount(Tour $tour, QuoteInput $input, float $peopleNet): float
    {
        if (! $input->campTypeId) {
            return 0.0;
        }

        $camp = $tour->campTypes()->active()->whereKey($input->campTypeId)->first();

        if (! $camp) {
            throw new BookingException('The selected camp type is not available for this tour.');
        }

        // Percent: share of the discounted people total. Fixed: per guest.
        return $camp->price_modifier_type === AdjustmentType::Percent
            ? $camp->modifierAmount($peopleNet)
            : round($camp->modifierAmount(0) * $input->guests(), 2);
    }

    private function vehicleAmount(Tour $tour, QuoteInput $input): float
    {
        if (! $input->vehicleId) {
            return 0.0;
        }

        $vehicle = $tour->vehicles()->active()->whereKey($input->vehicleId)->first();

        if (! $vehicle) {
            throw new BookingException('The selected vehicle is not available for this tour.');
        }

        return round((float) $vehicle->price_modifier, 2);
    }

    /** @return array{0: list<array{addon_id:int,name:string,unit_price:float,quantity:int,total:float}>, 1: float} */
    private function addonLines(Tour $tour, QuoteInput $input): array
    {
        if ($input->addons === []) {
            return [[], 0.0];
        }

        $found = $tour->addons()
            ->active()
            ->whereIn('addons.id', array_keys($input->addons))
            ->get()
            ->keyBy('id');

        $lines = [];
        $total = 0.0;

        foreach ($input->addons as $addonId => $quantity) {
            $addon = $found->get($addonId);

            if (! $addon) {
                throw new BookingException('One of the selected add-ons is not available for this tour.');
            }

            $unit = (float) ($addon->pivot->price_override ?? $addon->price);
            $lineTotal = round($unit * $quantity, 2);

            $lines[] = [
                'addon_id' => (int) $addon->id,
                'name' => (string) $addon->name,
                'unit_price' => $unit,
                'quantity' => $quantity,
                'total' => $lineTotal,
            ];

            $total += $lineTotal;
        }

        return [$lines, round($total, 2)];
    }

    /** @return array{0: ?Coupon, 1: float, 2: ?string} coupon, discount, error */
    private function coupon(?string $code, float $base): array
    {
        $code = $code ? strtoupper(trim($code)) : null;

        if (! $code) {
            return [null, 0.0, null];
        }

        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon || ! $coupon->isCurrentlyValid()) {
            return [null, 0.0, 'This coupon code is not valid or has expired.'];
        }

        if ($base < (float) $coupon->min_amount) {
            return [null, 0.0, 'This coupon needs a minimum order of '.number_format((float) $coupon->min_amount).'.'];
        }

        $value = (float) $coupon->value;

        $discount = $coupon->type === AdjustmentType::Percent
            ? $base * min(100.0, $value) / 100
            : min($base, $value);

        return [$coupon, round($discount, 2), null];
    }
}