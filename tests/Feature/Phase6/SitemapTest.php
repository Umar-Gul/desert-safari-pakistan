<?php

declare(strict_types=1);

namespace Tests\Feature\Phase6;

use App\Models\Blog;
use App\Support\CacheKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    public function test_sitemap_lists_public_pages_and_hides_inactive_content(): void
    {
        $active = $this->makeTour();
        $hidden = $this->makeTour(['is_active' => false]);
        $post = Blog::factory()->create();
        $draft = Blog::factory()->draft()->create();

        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee(route('home'), false)
            ->assertSee(route('tours.show', $active->slug), false)
            ->assertSee(route('categories.show', $active->category->slug), false)
            ->assertSee(route('blog.show', $post->slug), false)
            ->assertDontSee(route('tours.show', $hidden->slug), false)
            ->assertDontSee(route('blog.show', $draft->slug), false);

        $this->assertNotFalse(simplexml_load_string($response->getContent()), 'Sitemap must be valid XML.');
    }

    public function test_sitemap_is_cached_and_cleared_when_content_changes(): void
    {
        $this->makeTour();

        $this->get('/sitemap.xml')->assertOk();
        $this->assertTrue(Cache::has(CacheKeys::SITEMAP));

        $new = $this->makeTour(['title' => 'Brand New Safari']);

        $this->assertFalse(Cache::has(CacheKeys::SITEMAP));
        $this->get('/sitemap.xml')->assertSee(route('tours.show', $new->slug), false);
    }

    public function test_robots_txt_points_to_the_sitemap_and_blocks_private_areas(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /tours/*/checkout')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }
}