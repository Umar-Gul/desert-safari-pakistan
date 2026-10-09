<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Desert;
use App\Models\Tour;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** One place that turns the public filter form into a query (tours, category, search). */
class TourListingQuery
{
    public const SORTS = ['popular', 'rating', 'price_asc', 'price_desc', 'newest'];

    private const FINAL_PRICE = 'tours.base_price_adult * (100 - tours.discount_percent) / 100';

    /** @return Builder<Tour> */
    public function build(Request $request, ?Category $category = null, ?Desert $desert = null): Builder
    {
        $s = $this->selected($request);
        $query = Tour::query()->active()->withCardRelations();

        if ($category) {
            $query->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $category->id));
        } elseif ($s['category'] !== '') {
            $query->whereHas('categories', fn (Builder $q) => $q->where('categories.slug', $s['category']));
        }

        if ($desert) {
            $query->where('tours.desert_id', $desert->id);
        } elseif ($s['desert'] !== '') {
            $query->whereHas('desert', fn (Builder $q) => $q->where('slug', $s['desert']));
        }

        match ($s['duration']) {
            'short' => $query->where('tours.duration_hours', '<=', 3),
            'half' => $query->whereBetween('tours.duration_hours', [3.01, 6]),
            'full' => $query->whereBetween('tours.duration_hours', [6.01, 12]),
            'overnight' => $query->where('tours.duration_hours', '>', 12),
            default => null,
        };

        if ($s['min_price'] !== null) {
            $query->whereRaw(self::FINAL_PRICE.' >= CAST(? AS DECIMAL(12,2))', [$s['min_price']]);
        }

        if ($s['max_price'] !== null) {
            $query->whereRaw(self::FINAL_PRICE.' <= CAST(? AS DECIMAL(12,2))', [$s['max_price']]);
        }

        if ($s['rating'] > 0) {
            $query->where('tours.rating_cached', '>=', $s['rating'])->where('tours.reviews_count_cached', '>', 0);
        }

        if ($s['q'] !== '') {
            $like = '%'.addcslashes($s['q'], '%_\\').'%';

            $query->where(fn (Builder $w) => $w
                ->where('tours.title', 'like', $like)
                ->orWhere('tours.short_description', 'like', $like)
                ->orWhereHas('desert', fn (Builder $d) => $d->where('name', 'like', $like))
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $like)));
        }

        match ($s['sort']) {
            'rating' => $query->orderByDesc('tours.rating_cached')->orderByDesc('tours.reviews_count_cached'),
            'price_asc' => $query->orderByRaw(self::FINAL_PRICE.' asc'),
            'price_desc' => $query->orderByRaw(self::FINAL_PRICE.' desc'),
            'newest' => $query->orderByDesc('tours.id'),
            default => $query->orderByDesc('tours.is_featured')->orderBy('tours.sort_order')->orderByDesc('tours.id'),
        };

        return $query;
    }

    /** @return array{q:string,category:string,desert:string,duration:string,min_price:?float,max_price:?float,rating:int,sort:string} */
    public function selected(Request $request): array
    {
        $duration = (string) $request->input('duration', '');
        $sort = (string) $request->input('sort', 'popular');
        $rating = (int) $request->input('rating', 0);

        return [
            'q' => trim((string) $request->input('q', '')),
            'category' => (string) $request->input('category', ''),
            'desert' => (string) $request->input('desert', ''),
            'duration' => in_array($duration, ['short', 'half', 'full', 'overnight'], true) ? $duration : '',
            'min_price' => is_numeric($request->input('min_price')) ? max(0.0, (float) $request->input('min_price')) : null,
            'max_price' => is_numeric($request->input('max_price')) ? max(0.0, (float) $request->input('max_price')) : null,
            'rating' => in_array($rating, [3, 4, 5], true) ? $rating : 0,
            'sort' => in_array($sort, self::SORTS, true) ? $sort : 'popular',
        ];
    }

    /** Select options for the filter form. Cached. */
    public function filterOptions(): array
    {
        return Cache::remember(CacheKeys::TOUR_LIST, 600, fn () => [
            'categories' => Category::query()->active()->ordered()->get(['id', 'name', 'slug']),
            'deserts' => Desert::query()->active()->ordered()->get(['id', 'name', 'slug']),
        ]);
    }
}