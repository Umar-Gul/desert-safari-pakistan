<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\QuoteRules;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ManualBookingRequest extends FormRequest
{
    use QuoteRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Booking::class) ?? false;
    }

    public function rules(): array
    {
        $phone = ['regex:/^[0-9+\-\s()]{7,20}$/'];

        return array_merge($this->quoteRules(), [
            'tour_id' => ['required', 'integer', 'exists:tours,id'],
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
            'idempotency_key' => ['required', 'string', 'max:64'],
            'confirm_now' => ['nullable', 'boolean'],
        ]);
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
            'whatsapp.regex' => 'Please enter a valid WhatsApp number.',
        ];
    }
}