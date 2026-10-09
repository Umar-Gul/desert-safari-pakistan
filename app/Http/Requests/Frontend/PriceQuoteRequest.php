<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use App\Http\Requests\Concerns\QuoteRules;
use Illuminate\Foundation\Http\FormRequest;

class PriceQuoteRequest extends FormRequest
{
    use QuoteRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->quoteRules();
    }
}