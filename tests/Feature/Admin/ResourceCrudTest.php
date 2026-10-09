<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Addon;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\EventPackage;
use App\Models\Faq;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        return User::factory()->create()->assignRole($role);
    }

    public function test_every_module_renders_its_list_and_create_pages(): void
    {
        $admin = $this->userWithRole('admin');

        foreach (array_keys(config('admin_resources')) as $key) {
            $this->assertSame(200, $this->actingAs($admin)->get(route("admin.{$key}.index"))->status(), "{$key} index");
            $this->assertSame(200, $this->actingAs($admin)->get(route("admin.{$key}.create"))->status(), "{$key} create");
        }
    }

    public function test_category_is_created_with_generated_slug_and_default_sort_order(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->post('/admin/categories', ['name' => 'Sunset Safari', 'is_active' => 1])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Sunset Safari', 'slug' => 'sunset-safari', 'sort_order' => 0, 'is_active' => 1]);

        $this->actingAs($admin)->post('/admin/categories', ['name' => 'Sunset Safari']);

        $this->assertDatabaseHas('categories', ['slug' => 'sunset-safari-2']);
    }

    public function test_editing_with_a_blank_slug_keeps_the_current_url(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($admin)
            ->put("/admin/categories/{$category->id}", ['name' => 'New Name', 'slug' => ''])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name', 'slug' => 'old-name']);
    }

    public function test_required_fields_are_validated(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post('/admin/categories', ['name' => ''])->assertSessionHasErrors('name');
        $this->actingAs($admin)->post('/admin/addons', ['name' => 'Henna', 'price' => ''])->assertSessionHasErrors('price');
    }

    public function test_toggle_flips_the_active_flag(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch("/admin/categories/{$category->id}/toggle")->assertSessionHas('success');

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_category_with_tours_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();
        Tour::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)->delete("/admin/categories/{$category->id}")->assertSessionHas('error');

        $this->assertNotNull(Category::find($category->id));
    }

    public function test_unused_category_is_soft_deleted(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();

        $this->actingAs($admin)->delete("/admin/categories/{$category->id}")->assertRedirect(route('admin.categories.index'));

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_coupon_code_is_saved_in_capitals_and_must_be_unique(): void
    {
        $admin = $this->userWithRole('admin');
        $data = ['code' => 'summer10', 'type' => 'percent', 'value' => 10];

        $this->actingAs($admin)->post('/admin/coupons', $data)->assertRedirect(route('admin.coupons.index'));

        $this->assertDatabaseHas('coupons', ['code' => 'SUMMER10']);

        $this->actingAs($admin)->post('/admin/coupons', $data)->assertSessionHasErrors('code');
        $this->assertSame(1, Coupon::count());
    }

    public function test_global_faq_ignores_a_tour_but_tour_faq_requires_one(): void
    {
        $admin = $this->userWithRole('admin');
        $tour = Tour::factory()->create();

        $this->actingAs($admin)->post('/admin/faqs', [
            'question' => 'Is lunch included?', 'answer' => 'Yes.', 'scope' => 'global', 'tour_id' => $tour->id,
        ]);

        $this->assertNull(Faq::firstOrFail()->tour_id);

        $this->actingAs($admin)->post('/admin/faqs', [
            'question' => 'Is parking free?', 'answer' => 'Yes.', 'scope' => 'tour',
        ])->assertSessionHasErrors('tour_id');
    }

    public function test_blog_post_gets_author_and_publish_date(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post('/admin/blogs', [
            'title' => 'Best time to visit Cholistan',
            'content' => '<p>Winter.</p>',
            'status' => 'published',
        ])->assertRedirect(route('admin.blogs.index'));

        $blog = Blog::firstOrFail();

        $this->assertSame($admin->id, $blog->author_id);
        $this->assertNotNull($blog->published_at);
        $this->assertSame('best-time-to-visit-cholistan', $blog->slug);
    }

    public function test_event_package_features_are_saved_one_per_line(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post('/admin/event-packages', [
            'title' => 'School Trip', 'adult_price' => 360, 'child_price' => 160,
            'features' => "Camping\nCompetitions and Prizes\n\nRides Included",
        ]);

        $this->assertSame(
            ['Camping', 'Competitions and Prizes', 'Rides Included'],
            EventPackage::firstOrFail()->features
        );
    }

    public function test_editor_can_edit_content_but_not_open_coupons(): void
    {
        $editor = $this->userWithRole('editor');

        $this->actingAs($editor)->post('/admin/categories', ['name' => 'Camel Safari'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Camel Safari']);

        $this->actingAs($editor)->get('/admin/coupons')->assertForbidden();
    }

    public function test_support_can_view_content_but_not_change_it(): void
    {
        $support = $this->userWithRole('support');

        $this->actingAs($support)->get('/admin/categories')->assertOk();
        $this->actingAs($support)->post('/admin/categories', ['name' => 'Nope'])->assertForbidden();
        $this->assertDatabaseMissing('categories', ['name' => 'Nope']);
    }

    public function test_addon_price_field_accepts_decimals(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post('/admin/addons', ['name' => 'Henna', 'price' => '1500.50']);

        $this->assertEquals(1500.50, Addon::firstOrFail()->price);
    }
}