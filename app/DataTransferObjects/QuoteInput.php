<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Carbon\CarbonImmutable;

/** Everything the price depends on. Built from a validated request. */
final readonly class QuoteInput
{
    /**
     * @param  array<int, int>  $addons  addon_id => quantity (only quantities > 0)
     */
    public function __construct(
        public CarbonImmutable $tourDate,
        public int $adults,
        public int $children = 0,
        public ?int $campTypeId = null,
        public ?int $vehicleId = null,
        public array $addons = [],
        public ?string $couponCode = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $addons = [];
        foreach ((array) ($data['addons'] ?? []) as $id => $quantity) {
            if ((int) $quantity > 0) {
                $addons[(int) $id] = (int) $quantity;
            }
        }

        return new self(
            tourDate: CarbonImmutable::parse($data['tour_date'])->startOfDay(),
            adults: (int) ($data['adults'] ?? 1),
            children: (int) ($data['children'] ?? 0),
            campTypeId: filled($data['camp_type_id'] ?? null) ? (int) $data['camp_type_id'] : null,
            vehicleId: filled($data['vehicle_id'] ?? null) ? (int) $data['vehicle_id'] : null,
            addons: $addons,
            couponCode: filled($data['coupon_code'] ?? null) ? strtoupper(trim((string) $data['coupon_code'])) : null,
        );
    }

    public function guests(): int
    {
        return $this->adults + $this->children;
    }
}