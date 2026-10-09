<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\FaqScope;
use App\Enums\TourBadge;
use App\Models\Addon;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Tour;
use App\Models\TourHighlight;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsStaff;
use Tests\TestCase;

class TourEditorTest extends TestCase
{
    use ActsAsStaff, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** @param array<string, mixed> $extra */
    private function payload(Category $category, array $extra = []): array
    {
        return array_merge([
            'category_id' => $category->id,
            'title' => 'Cholistan Sunset Safari',
            'price_label' => 'per person',
            'base_price_adult' => '10000',
            'base_price_child' => '6000',
            'discount_percent' => '10',
            'is_active' => '1',
            'badges' => ['top_rated'],
            'description' => '<p>Dunes at golden hour.</p><script>alert(1)</script>',
            'highlights' => [['text' => 'Pickup by SUV', 'icon' => ''], ['text' => '', 'icon' => '']],
            'itinerary' => [['time_label' => '4:00 PM', 'title' => 'Pickup', 'description' => '']],
            'inclusions' => [['text' => 'Dinner'], ['text' => '']],
            'exclusions' => [['text' => 'Tips']],
            'additional_infos' => [['title' => 'Bring', 'content' => 'Sunglasses']],
            'faqs' => [['question' => 'Is the food halal?', 'answer' => 'Yes.']],
        ], $extra);
    }

    public function test_admin_creates_a_tour_with_all_its_parts(): void
    {
        $category = Category::factory()->create();
        $other = Category::factory()->create();
        $addon = Addon::factory()->create();
        $camp = CampType::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->actingAs($this->staff('admin'))->post('/admin/tours', $this->payload($category, [
            'category_ids' => [$other->id],
            'addon_ids' => [$addon->id],
            'addon_prices' => [$addon->id => '750'],
            'camp_type_ids' => [$camp->id],
            'vehicle_ids' => [$vehicle->id],
        ]));

        $response->assertSessionHasNoErrors();

        $tour = Tour::query()->where('title', 'Cholistan Sunset Safari')->firstOrFail();

        $response->assertRedirect(route('admin.tours.edit', $tour->id));

        $this->assertSame('cholistan-sunset-safari', $tour->slug);
        $this->assertSame(1, $tour->highlights()->count());
        $this->assertSame(1, $tour->itineraryItems()->count());
        $this->assertSame(1, $tour->inclusions()->count());
        $this->assertSame(1, $tour->exclusions()->count());
        $this->assertSame(1, $tour->additionalInfos()->count());
        $this->assertSame(1, Faq::where('tour_id', $tour->id)->where('scope', FaqScope::Tour->value)->count());
        $this->assertSame(TourBadge::TopRated, $tour->badges->first());
        $this->assertStringNotContainsString('<script', (string) $tour->description);
        $this->assertEquals(750, $tour->addons()->first()->pivot->price_override);
        $this->assertSame(1, $tour->campTypes()->count());
        $this->assertSame(1, $tour->vehicles()->count());
        $this->assertEqualsCanonicalizing([$category->id, $other->id], $tour->categories()->pluck('categories.id')->all());
    }

    public function test_updating_replaces_lists_and_keeps_the_slug_when_blank(): void
    {
        $tour = Tour::factory()->create(['slug' => 'keep-me']);
        TourHighlight::create(['tour_id' => $tour->id, 'text' => 'Old one', 'sort_order' => 0]);
        TourHighlight::create(['tour_id' => $tour->id, 'text' => 'Old two', 'sort_order' => 1]);

        $category = Category::findOrFail($tour->category_id);

        $this->actingAs($this->staff('editor'))
            ->put("/admin/tours/{$tour->id}", $this->payload($category, [
                'slug' => '',
                'highlights' => [['text' => 'New only', 'icon' => '']],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tours.edit', $tour->id));

        $this->assertSame('keep-me', $tour->fresh()->slug);
        $this->assertSame(['New only'], TourHighlight::where('tour_id', $tour->id)->pluck('text')->all());
    }

    public function test_required_fields_and_unique_slug_are_validated(): void
    {
        $admin = $this->staff('admin');

        $this->actingAs($admin)->post('/admin/tours', [])
            ->assertSessionHasErrors(['category_id', 'title', 'base_price_adult', 'price_label']);

        Tour::factory()->create(['slug' => 'taken']);

        $this->actingAs($admin)->post('/admin/tours', $this->payload(Category::factory()->create(), ['slug' => 'taken']))
            ->assertSessionHasErrors('slug');
    }

    public function test_faq_with_a_question_needs_an_answer(): void
    {
        $this->actingAs($this->staff('admin'))
            ->post('/admin/tours', $this->payload(Category::factory()->create(), [
                'faqs' => [['question' => 'Anything?', 'answer' => '']],
            ]))
            ->assertSessionHasErrors('faqs.0.answer');
    }

    public function test_create_and_edit_pages_render(): void
    {
        $tour = Tour::factory()->create();
        $admin = $this->staff('admin');

        $this->actingAs($admin)->get('/admin/tours')->assertOk()->assertSee($tour->title);
        $this->actingAs($admin)->get('/admin/tours/create')->assertOk()->assertSee('New tour');
        $this->actingAs($admin)->get("/admin/tours/{$tour->id}/edit")->assertOk()->assertSee($tour->title);
    }

    public function test_toggle_and_delete(): void
    {
        $tour = Tour::factory()->create(['is_active' => true]);
        $admin = $this->staff('admin');

        $this->actingAs($admin)->patch("/admin/tours/{$tour->id}/toggle")->assertRedirect();
        $this->assertFalse($tour->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/tours/{$tour->id}")->assertRedirect(route('admin.tours.index'));
        $this->assertSoftDeleted($tour);
    }

    public function test_support_can_view_the_list_but_not_create(): void
    {
        $support = $this->staff('support');

        $this->actingAs($support)->get('/admin/tours')->assertOk();
        $this->actingAs($support)->get('/admin/tours/create')->assertForbidden();
        $this->actingAs($support)->post('/admin/tours', $this->payload(Category::factory()->create()))->assertForbidden();
    }
}