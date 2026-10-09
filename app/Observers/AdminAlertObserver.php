<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\ReviewStatus;
use App\Models\Enquiry;
use App\Models\Question;
use App\Models\Review;
use App\Models\Tour;
use App\Notifications\AdminAlert;
use App\Support\AdminMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminAlertObserver
{
    public function created(Model $model): void
    {
        match (true) {
            $model instanceof Enquiry => $this->enquiry($model),
            $model instanceof Review && $model->status === ReviewStatus::Pending => $this->review($model),
            $model instanceof Question && $model->status === ReviewStatus::Pending => $this->question($model),
            default => null,
        };
    }

    private function enquiry(Enquiry $enquiry): void
    {
        AdminMail::alert(new AdminAlert(
            'New enquiry: '.$enquiry->subject,
            [
                "From: {$enquiry->name} <{$enquiry->email}>".($enquiry->phone ? " | {$enquiry->phone}" : ''),
                Str::limit($enquiry->message, 500),
            ],
            'Open enquiries',
            route('admin.enquiries.index'),
        ));
    }

    private function review(Review $review): void
    {
        $tour = Tour::query()->whereKey($review->tour_id)->value('title');

        AdminAlert::class;

        AdminMail::alert(new AdminAlert(
            'New review waiting for approval',
            [
                "{$review->name} rated \"{$tour}\" {$review->rating}/5.",
                Str::limit($review->comment, 500),
            ],
            'Moderate reviews',
            route('admin.reviews.index'),
        ));
    }

    private function question(Question $question): void
    {
        AdminMail::alert(new AdminAlert(
            'New question waiting for an answer',
            [
                "{$question->name} <{$question->email}> asked:",
                Str::limit($question->question, 500),
            ],
            'Answer questions',
            route('admin.questions.index'),
        ));
    }
}