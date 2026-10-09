<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Review::class);

        $filter = (string) $request->input('status', 'pending');
        $status = ReviewStatus::tryFrom($filter);

        return view('admin.reviews.index', [
            'reviews' => Review::query()
                ->with('tour:id,title')
                ->when($status, fn ($q) => $q->where('status', $status->value))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'filter' => $filter,
            'counts' => Review::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function status(Request $request, Review $review): RedirectResponse
    {
        Gate::authorize('update', $review);

        $data = $request->validate(['status' => ['required', Rule::enum(ReviewStatus::class)]]);

        // The ReviewObserver refreshes the tour's cached rating.
        $review->update(['status' => ReviewStatus::from($data['status'])]);

        return back()->with('success', 'Review '.$review->status->label()."."); 
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}