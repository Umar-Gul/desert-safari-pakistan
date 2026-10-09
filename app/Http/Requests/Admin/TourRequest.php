<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TourBadge;
use App\Models\Tour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TourRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Create and update use the same roles in CatalogPolicy.
        return $this->user()?->can('create', Tour::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => filled($this->input('slug')) ? Str::slug((string) $this->input('slug')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'desert_id' => ['nullable', 'integer', 'exists:deserts,id'],
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('tours', 'slug')->ignore($this->route('tour'))],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:100000'],
            'duration_hours' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'base_price_adult' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'base_price_child' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'discount_percent' => ['nullable', 'integer', 'between:0,100'],
            'price_label' => ['required', 'string', 'max:50'],
            'max_group_size' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'min_age' => ['nullable', 'integer', 'between:0,99'],
            'badges' => ['nullable', 'array'],
            'badges.*' => [Rule::enum(TourBadge::class)],
            'cancellation_policy' => ['nullable', 'string', 'max:50000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],

            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical' => ['nullable', 'url', 'max:255'],
            'og_image' => ['nullable', 'url', 'max:255'],

            'highlights' => ['nullable', 'array', 'max:40'],
            'highlights.*.text' => ['nullable', 'string', 'max:255'],
            'highlights.*.icon' => ['nullable', 'string', 'max:50'],

            'itinerary' => ['nullable', 'array', 'max:40'],
            'itinerary.*.time_label' => ['nullable', 'string', 'max:50'],
            'itinerary.*.title' => ['nullable', 'string', 'max:190'],
            'itinerary.*.description' => ['nullable', 'string', 'max:2000'],

            'inclusions' => ['nullable', 'array', 'max:60'],
            'inclusions.*.text' => ['nullable', 'string', 'max:255'],
            'exclusions' => ['nullable', 'array', 'max:60'],
            'exclusions.*.text' => ['nullable', 'string', 'max:255'],

            'additional_infos' => ['nullable', 'array', 'max:30'],
            'additional_infos.*.title' => ['nullable', 'string', 'max:190'],
            'additional_infos.*.content' => ['nullable', 'string', 'max:3000'],

            'faqs' => ['nullable', 'array', 'max:40'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'string', 'max:3000', 'required_with:faqs.*.question'],

            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:addons,id'],
            'addon_prices' => ['nullable', 'array'],
            'addon_prices.*' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'camp_type_ids' => ['nullable', 'array'],
            'camp_type_ids.*' => ['integer', 'exists:camp_types,id'],
            'vehicle_ids' => ['nullable', 'array'],
            'vehicle_ids.*' => ['integer', 'exists:vehicles,id'],

            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_alt' => ['nullable', 'string', 'max:160'],
            'remove_cover' => ['nullable', 'boolean'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'faqs.*.answer.required_with' => 'Each FAQ with a question needs an answer.',
        ];
    }
}