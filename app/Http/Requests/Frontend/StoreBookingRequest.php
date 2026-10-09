<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use App\Http\Requests\Concerns\QuoteRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreBookingRequest extends FormRequest
{
    use QuoteRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $phone = ['regex:/^[0-9+\-\s()]{7,20}$/'];

        return array_merge($this->quoteRules(), [
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => array_merge(['required', 'string'], $phone),
            'whatsapp' => array_merge(['nullable', 'string'], $phone),
            'nationality' => ['nullable', 'string', 'max:100'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'pickup_address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'terms' => ['accepted'],
            // Generated once when the checkout page renders; stops double submits.
            'idempotency_key' => ['required', 'string', 'max:64'],
            // Honeypot: real users never fill this in.
            'website' => ['nullable', 'max:0'],
        ]);
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => 'Please accept the terms and conditions to continue.',
            'phone.regex' => 'Please enter a valid phone number.',
            'whatsapp.regex' => 'Please enter a valid WhatsApp number.',
        ];
    }

    /** @return array<string, mixed> */
    public function customerData(): array
    {
        return Arr::only($this->validated(), [
            'customer_name', 'email', 'phone', 'whatsapp', 'nationality',
            'pickup_time', 'pickup_location', 'pickup_address', 'city', 'special_requests',
        ]);
    }

    public function idempotencyKey(): string
    {
        return (string) $this->validated('idempotency_key');
    }
}