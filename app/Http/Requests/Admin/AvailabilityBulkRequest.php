<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Availability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AvailabilityBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Availability::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'tour_id' => ['required', 'integer', 'exists:tours,id'],
            'from' => ['required', 'date', 'after_or_equal:today'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'action' => ['required', Rule::in(['set', 'close', 'open', 'delete'])],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:0,6'],
            'capacity' => ['required_if:action,set', 'nullable', 'integer', 'min:0', 'max:10000'],
            'price_override' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'clear_price' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            if (\Carbon\Carbon::parse($this->input('from'))->diffInDays(\Carbon\Carbon::parse($this->input('to'))) > 365) {
                $v->errors()->add('to', 'Please pick a range of at most one year.');
            }
        });
    }
}