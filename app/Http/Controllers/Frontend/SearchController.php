<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\TourListingQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, TourListingQuery $listing): View
    {
        $selected = $listing->selected($request);

        return view('frontend.search', [
            'term' => $selected['q'],
            'tours' => $selected['q'] === '' ? null : $listing->build($request)->paginate(12)->withQueryString(),
            'filters' => $listing->filterOptions(),
            'selected' => $selected,
            'categories' => Category::query()->active()->ordered()->limit(8)->get(['id', 'name', 'slug']),
        ]);
    }
}
