<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;

enum PaymentMethod: string
{
    use EnumHelpers;

    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case JazzCash = 'jazzcash';
    case Easypaisa = 'easypaisa';
    case Card = 'card';
    case PaymentLink = 'payment_link';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash on arrival',
            self::BankTransfer => 'Bank transfer',
            self::JazzCash => 'JazzCash',
            self::Easypaisa => 'Easypaisa',
            self::Card => 'Card',
            self::PaymentLink => 'Payment link',
        };
    }
}