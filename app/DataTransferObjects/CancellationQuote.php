<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class CancellationQuote
{
    public function __construct(
        public bool $isFree,
        public float $feePercent,
        public float $feeAmount,
        public float $hoursBeforeStart,
    ) {}
}