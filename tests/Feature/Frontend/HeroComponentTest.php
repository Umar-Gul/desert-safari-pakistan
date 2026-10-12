<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Category;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class HeroComponentTest extends TestCase
{
    use RefreshDatabase;

    private function tourIn(Category $category, array $overrides = []): Tour
    {
        return Tour::factory()->create(array_merge([
            'category_id' => $category->id,
            'is_active' => true,
            'base_price_adult' => 10000,
            'discount_percent' => 0,
        ], $overrides));
    }

    private function render(string $attributes = ''): string
    {
        return Blade::render("<x-home.hero {$attributes} />");
    }

    public function test_it_renders_the_headline_texts(): void
    {
        $html = $this->render('title="Dunes and Dreams" subtitle="Camp under the stars" eyebrow="From dunes to dreams"');

        $this->assertStringContainsString('Dunes and Dreams', $html);
        $this->assertStringContainsString('Camp under the stars', $html);
        $this->assertStringContainsString('From dunes to dreams', $html);
        $this->assertStringContainsString('Explore all tours', $html);
    }

    public function test_cards_show_tour_count_and_cheapest_price_after_discount(): void
    {
        $category = Category::factory()->create(['name' => 'Camel Safari', 'is_quick_link' => true]);
        $this->tourIn($category, ['base_price_adult' => 10000, 'discount_percent' => 20]); // 8,000
        $this->tourIn($category, ['base_price_adult' => 5000]);                            // 5,000
        $this->tourIn($category, ['base_price_adult' => 1000, 'is_active' => false]);      // ignored

        $html = $this->render('title="Hero"');

        $this->assertStringContainsString('Camel Safari', $html);
        $this->assertStringContainsString('2 tours', $html);
        $this->assertStringContainsString('PKR 5,000', $html);
    }

    public function test_categories_without_active_tours_are_not_shown(): void
    {
        $empty = Category::factory()->create(['name' => 'Empty Category', 'is_quick_link' => true]);
        $onlyHidden = Category::factory()->create(['name' => 'Hidden Tours Only', 'is_quick_link' => true]);
        $this->tourIn($onlyHidden, ['is_active' => false]);

        $html = $this->render('title="Hero"');

        $this->assertStringNotContainsString($empty->name, $html);
        $this->assertStringNotContainsString('Hidden Tours Only', $html);
    }

    public function test_quick_link_categories_come_first_and_the_limit_is_respected(): void
    {
        $plain = Category::factory()->create(['name' => 'Plain Category', 'sort_order' => 1, 'is_quick_link' => false]);
        $first = Category::factory()->create(['name' => 'Quick One', 'sort_order' => 5, 'is_quick_link' => true]);
        $second = Category::factory()->create(['name' => 'Quick Two', 'sort_order' => 6, 'is_quick_link' => true]);

        foreach ([$plain, $first, $second] as $category) {
            $this->tourIn($category);
        }

        $html = $this->render('title="Hero" :limit="2"');

        $this->assertStringContainsString('Quick One', $html);
        $this->assertStringContainsString('Quick Two', $html);
        $this->assertStringNotContainsString('Plain Category', $html);
    }

    public function test_one_tour_uses_singular_wording(): void
    {
        $category = Category::factory()->create(['is_quick_link' => true]);
        $this->tourIn($category);

        $this->assertStringContainsString('1 tour ', $this->render('title="Hero"'));
    }
}