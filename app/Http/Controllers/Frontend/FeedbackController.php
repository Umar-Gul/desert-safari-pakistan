<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreQuestionRequest;
use App\Http\Requests\Frontend\StoreReviewRequest;
use App\Models\Question;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

/** Public questions and reviews. Both wait for staff approval before they appear. */
class FeedbackController extends Controller
{
    public function question(StoreQuestionRequest $request): RedirectResponse
    {
        $v = $request->validated();

        Question::create([
            ...Arr::only($v, ['tour_id', 'name', 'email', 'question']),
            'status' => ReviewStatus::Pending,
            'ip' => $request->ip(),
        ]);

        $message = 'Thanks! Your question was received and will appear once we have answered it.';

        if (! empty($v['tour_id'])) {
            $slug = Tour::query()->whereKey($v['tour_id'])->value('slug');

            if ($slug) {
                return redirect()->route('tours.show', ['slug' => $slug, 'tab' => 'faq'])->with('status', $message);
            }
        }

        return back()->with('status', $message);
    }

    public function review(StoreReviewRequest $request, string $slug): RedirectResponse
    {
        $tour = Tour::query()->active()->where('slug', $slug)->firstOrFail();

        Review::create([
            ...Arr::only($request->validated(), ['name', 'country', 'rating', 'title', 'comment', 'visited_on']),
            'tour_id' => $tour->id,
            'status' => ReviewStatus::Pending,
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('tours.show', ['slug' => $tour->slug, 'tab' => 'reviews'])
            ->with('status', 'Thank you for your review! It will be published after a quick check.');
    }
}