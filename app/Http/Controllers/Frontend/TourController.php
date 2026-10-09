<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\AdjustmentType;
use App\Http\Controllers\Controller;
use App\Models\CampType;
use App\Models\Tour;
use App\Services\AvailabilityService;
use App\Services\TourListingQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(Request $request, TourListingQuery $listing): View
    {
        return view('frontend.tours.index', [
            'tours' => $listing->build($request)->paginate(12)->withQueryString(),
            'filters' => $listing->filterOptions(),
            'selected' => $listing->selected($request),
        ]);
    }

    public function show(Request $request, string $slug, AvailabilityService $availability): View
    {
        $tour = Tour::query()->active()->where('slug', $slug)->with([
            'category:id,name,slug',
            'desert:id,name,slug',
            'media',
            'highlights', 'itineraryItems', 'inclusions', 'exclusions', 'additionalInfos',
            'faqs' => fn ($q) => $q->where('is_active', true),
            'addons' => fn ($q) => $q->active()->ordered(),
            'campTypes' => fn ($q) => $q->active()->ordered(),
            'vehicles' => fn ($q) => $q->active()->ordered(),
            'approvedReviews' => fn ($q) => $q->limit(10),
            'approvedQuestions' => fn ($q) => $q->limit(10),
        ])->firstOrFail();

        $related = Tour::query()->active()->withCardRelations()
            ->where('category_id', $tour->category_id)
            ->whereKeyNot($tour->id)
            ->ordered()
            ->limit(4)
            ->get();

        $map = $availability->calendar($tour, today(), today()->addDays(120))->all();

        $widget = [
            'quoteUrl' => route('tours.price', $tour->slug),
            'checkoutUrl' => route('tours.checkout', $tour->slug),
            'token' => csrf_token(),
            'symbol' => (string) setting('currency_symbol', 'Rs'),
            'today' => today()->toDateString(),
            'lastDate' => today()->addDays(120)->toDateString(),
            'maxGuests' => (int) ($tour->max_group_size ?: config('booking.max_guests_per_booking', 50)),
            'availability' => $map,
            'campTypes' => $tour->campTypes->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'note' => $this->campNote($c)])->values()->all(),
            'vehicles' => $tour->vehicles->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'note' => (float) $v->price_modifier > 0 ? '+ '.money($v->price_modifier) : '',
            ])->values()->all(),
            'addons' => $tour->addons->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'price' => (float) ($a->pivot->price_override ?? $a->price),
            ])->values()->all(),
            'initial' => [
                'date' => (string) old('tour_date', $request->query('tour_date', '')),
                'adults' => (int) old('adults', $request->query('adults', 2)),
                'children' => (int) old('children', $request->query('children', 0)),
                'camp_type_id' => old('camp_type_id', $request->query('camp_type_id')),
                'vehicle_id' => old('vehicle_id', $request->query('vehicle_id')),
                'addons' => array_map('intval', (array) old('addons', $request->query('addons', []))),
                'coupon_code' => (string) old('coupon_code', $request->query('coupon_code', '')),
            ],
        ];

        return view('frontend.tours.show', compact('tour', 'related', 'widget'));
    }

    private function campNote(CampType $camp): string
    {
        $value = (float) $camp->price_modifier_value;

        if ($value <= 0) {
            return '';
        }

        return $camp->price_modifier_type === AdjustmentType::Percent
            ? '+'.rtrim(rtrim(number_format($value, 2), '0'), '.').'%'
            : '+ '.money($value).' / guest';
    }
}