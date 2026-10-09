<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Question::class);

        $filter = (string) $request->input('status', 'pending');
        $status = ReviewStatus::tryFrom($filter);

        return view('admin.questions.index', [
            'questions' => Question::query()
                ->with('tour:id,title')
                ->when($status, fn ($q) => $q->where('status', $status->value))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'filter' => $filter,
            'counts' => Question::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        Gate::authorize('update', $question);

        $data = $request->validate([
            'answer' => ['nullable', 'string', 'max:3000', 'required_if:status,approved'],
            'status' => ['required', Rule::enum(ReviewStatus::class)],
        ]);

        $answer = isset($data['answer']) ? trim($data['answer']) : null;

        $question->update([
            'answer' => $answer ?: null,
            'status' => ReviewStatus::from($data['status']),
            'answered_at' => $answer
                ? ($question->answer === $answer && $question->answered_at ? $question->answered_at : now())
                : null,
        ]);

        return back()->with('success', 'Question saved.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        Gate::authorize('delete', $question);

        $question->delete();

        return back()->with('success', 'Question deleted.');
    }
}