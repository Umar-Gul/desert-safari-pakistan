<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\EnquiryStatus;
use App\Enums\ReviewStatus;
use App\Models\Enquiry;
use App\Models\Question;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsStaff;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use ActsAsStaff, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_approving_a_review_updates_the_tour_rating(): void
    {
        $tour = Tour::factory()->create();
        $review = Review::factory()->create(['tour_id' => $tour->id, 'rating' => 4]);

        $this->assertSame(0, $tour->fresh()->reviews_count_cached);

        $this->actingAs($this->staff('support'))
            ->patch("/admin/reviews/{$review->id}/status", ['status' => 'approved'])
            ->assertSessionHasNoErrors();

        $tour->refresh();

        $this->assertSame(ReviewStatus::Approved, $review->fresh()->status);
        $this->assertSame(1, $tour->reviews_count_cached);
        $this->assertEquals(4.0, (float) $tour->rating_cached);
    }

    public function test_review_lists_render_and_editors_cannot_change_them(): void
    {
        $review = Review::factory()->create(['comment' => 'Wonderful night under the stars.']);

        $this->actingAs($this->staff('editor'))->get('/admin/reviews')->assertOk()->assertSee('Wonderful night');

        $this->actingAs($this->staff('editor'))
            ->patch("/admin/reviews/{$review->id}/status", ['status' => 'approved'])
            ->assertForbidden();
    }

    public function test_a_question_needs_an_answer_before_approval(): void
    {
        $question = Question::factory()->create();
        $admin = $this->staff('admin');

        $this->actingAs($admin)->put("/admin/questions/{$question->id}", ['status' => 'approved', 'answer' => ''])
            ->assertSessionHasErrors('answer');

        $this->actingAs($admin)->put("/admin/questions/{$question->id}", ['status' => 'approved', 'answer' => 'Yes, halal dinner is served.'])
            ->assertSessionHasNoErrors();

        $question->refresh();

        $this->assertSame(ReviewStatus::Approved, $question->status);
        $this->assertSame('Yes, halal dinner is served.', $question->answer);
        $this->assertNotNull($question->answered_at);
    }

    public function test_enquiry_status_changes_and_page_renders(): void
    {
        $enquiry = Enquiry::factory()->create(['subject' => 'School trip for 40 students']);
        $support = $this->staff('support');

        $this->actingAs($support)->get('/admin/enquiries')->assertOk()->assertSee('School trip for 40 students');

        $this->actingAs($support)->patch("/admin/enquiries/{$enquiry->id}/status", ['status' => 'read'])->assertRedirect();

        $this->assertSame(EnquiryStatus::Read, $enquiry->fresh()->status);
    }

    public function test_reviews_can_be_deleted_by_support(): void
    {
        $review = Review::factory()->create();

        $this->actingAs($this->staff('support'))->delete("/admin/reviews/{$review->id}")->assertRedirect();

        $this->assertSoftDeleted($review);
    }
}