<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\TourListingQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug, TourListingQuery $listing): View
    {
        $category = Category::query()->active()->where('slug', $slug)->with('media')->firstOrFail();

        return view('frontend.categories.show', [
            'category' => $category,
            'tours' => $listing->build($request, $category)->paginate(12)->withQueryString(),
            'filters' => $listing->filterOptions(),
            'selected' => $listing->selected($request),
        ]);
    }
}