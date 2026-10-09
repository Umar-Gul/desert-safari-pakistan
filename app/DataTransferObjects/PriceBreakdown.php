<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Result of PricingService. Maps onto the bookings price snapshot:
 * subtotal, addonsTotal, discountTotal, tax, grandTotal.
 */
final readonly class PriceBreakdown
{
    /**
     * @param  list<array{addon_id:int,name:string,unit_price:float,quantity:int,total:float}>  $addonLines
     */
    public function __construct(
        public string $currency,
        public int $adults,
        public int $children,
        public float $adultUnitBase,
        public float $childUnitBase,
        public float $peopleGross,
        public float $tourDiscount,
        public float $campAmount,
        public float $vehicleAmount,
        public array $addonLines,
        public float $addonsTotal,
        public ?int $couponId,
        public ?string $couponCode,
        public float $couponDiscount,
        public ?string $couponError,
        public float $subtotal,
        public float $discountTotal,
        public float $taxPercent,
        public float $tax,
        public float $grandTotal,
    ) {}

    /** People total after the tour's own discount. */
    public function peopleNet(): float
    {
        return round($this->peopleGross - $this->tourDiscount, 2);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'adults' => $this->adults,
            'children' => $this->children,
            'adult_unit_base' => $this->adultUnitBase,
            'child_unit_base' => $this->childUnitBase,
            'people_gross' => $this->peopleGross,
            'tour_discount' => $this->tourDiscount,
            'people_net' => $this->peopleNet(),
            'camp_amount' => $this->campAmount,
            'vehicle_amount' => $this->vehicleAmount,
            'addon_lines' => $this->addonLines,
            'addons_total' => $this->addonsTotal,
            'coupon_code' => $this->couponCode,
            'coupon_discount' => $this->couponDiscount,
            'coupon_error' => $this->couponError,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discountTotal,
            'tax_percent' => $this->taxPercent,
            'tax' => $this->tax,
            'grand_total' => $this->grandTotal,
        ];
    }
}
