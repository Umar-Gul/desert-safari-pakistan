<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentTransactionStatus;

final readonly class PaymentResult
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public PaymentTransactionStatus $status,
        public ?string $transactionId = null,
        public array $payload = [],
        public ?string $message = null,
        public ?string $redirectUrl = null,
    ) {}
}