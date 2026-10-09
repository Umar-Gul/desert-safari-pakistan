<?php

declare(strict_types=1);

namespace Tests\Feature\Phase6;

use App\Models\Blog;
use App\Models\Faq;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->setUpBookingConfig();
    }

    /** @return list<array<string, mixed>> */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return array_map(fn (string $json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $matches[1]);
    }

    /** @param list<array<string, mixed>> $blocks */
    private function block(array $blocks, string $type): ?array
    {
        return collect($blocks)->first(fn ($b) => in_array($type, (array) ($b['@type'] ?? []), true));
    }

    public function test_home_page_has_agency_website_and_faq_markup(): void
    {
        Faq::factory()->create(['question' => 'Is the dinner halal?', 'answer' => 'Yes.']);

        $blocks = $this->jsonLd($this->get('/')->assertOk()->getContent());

        $this->assertNotNull($this->block($blocks, 'TravelAgency'));
        $this->assertNotNull($this->block($blocks, 'WebSite'));
        $this->assertSame('Is the dinner halal?', $this->block($blocks, 'FAQPage')['mainEntity'][0]['name']);
    }

    public function test_tour_page_has_trip_offer_rating_faq_and_breadcrumbs(): void
    {
        $tour = $this->makeTour(['title' => 'Camel Sunset Trek']);
        Faq::factory()->forTour($tour)->create(['question' => 'Is the food halal?', 'answer' => 'Yes, all food is halal.']);
        Review::factory()->approved()->create(['tour_id' => $tour->id, 'rating' => 5, 'name' => 'Sara', 'comment' => 'Lovely evening in the dunes.']);

        $blocks = $this->jsonLd($this->get('/tours/'.$tour->slug)->assertOk()->getContent());

        $trip = $this->block($blocks, 'TouristTrip');
        $this->assertNotNull($trip);
        $this->assertContains('Product', $trip['@type']);
        $this->assertSame('9000.00', $trip['offers']['price']);
        $this->assertSame('PKR', $trip['offers']['priceCurrency']);
        $this->assertSame('5.0', $trip['aggregateRating']['ratingValue']);
        $this->assertSame(1, $trip['aggregateRating']['reviewCount']);
        $this->assertSame('Sara', $trip['review'][0]['author']['name']);

        $this->assertSame('Is the food halal?', $this->block($blocks, 'FAQPage')['mainEntity'][0]['name']);

        $crumbs = $this->block($blocks, 'BreadcrumbList');
        $this->assertCount(4, $crumbs['itemListElement']);
        $this->assertSame(1, $crumbs['itemListElement'][0]['position']);
        $this->assertArrayNotHasKey('item', $crumbs['itemListElement'][3]);
    }

    public function test_blog_post_has_blogposting_markup(): void
    {
        $post = Blog::factory()->create(['title' => 'Packing list for the desert']);

        $blocks = $this->jsonLd($this->get('/blog/'.$post->slug)->assertOk()->getContent());

        $this->assertSame('Packing list for the desert', $this->block($blocks, 'BlogPosting')['headline']);
        $this->assertNotNull($this->block($blocks, 'BreadcrumbList'));
    }

    public function test_json_ld_cannot_be_used_to_break_out_of_the_script_tag(): void
    {
        $tour = $this->makeTour(['title' => 'Evil </script><script>alert(1)</script> Tour']);

        $response = $this->get('/tours/'.$tour->slug)->assertOk();

        $response->assertDontSee('</script><script>alert(1)', false);
        $this->assertNotEmpty($this->jsonLd($response->getContent())); // still valid JSON
    }

    public function test_meta_tags_use_the_tour_seo_fields_and_site_twitter_handle(): void
    {
        Setting::updateOrCreate(['key' => 'twitter_handle'], ['group' => 'social', 'type' => 'string', 'value' => '@desertsafaripk']);

        $tour = $this->makeTour([
            'meta_title' => 'Custom Sunset Title',
            'meta_description' => 'A custom meta description for search engines.',
            'canonical' => 'https://example.test/custom-canonical',
        ]);

        $this->get('/tours/'.$tour->slug)
            ->assertOk()
            ->assertSee('<title>Custom Sunset Title', false)
            ->assertSee('<meta name="description" content="A custom meta description for search engines.">', false)
            ->assertSee('<link rel="canonical" href="https://example.test/custom-canonical">', false)
            ->assertSee('<meta name="twitter:site" content="@desertsafaripk">', false);
    }
}