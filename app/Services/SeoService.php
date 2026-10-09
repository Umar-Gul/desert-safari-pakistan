<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Blog;
use App\Models\Tour;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Builds schema.org JSON-LD arrays. Views render them with <x-seo.jsonld>. */
class SeoService
{
    /** @return array<string, mixed> */
    public function organization(): array
    {
        $sameAs = array_values(array_filter([
            setting('facebook_url'), setting('instagram_url'), setting('youtube_url'),
            setting('tiktok_url'), setting('tripadvisor_url'),
        ]));

        $image = $this->siteImage();

        return $this->clean([
            '@context' => 'https://schema.org',
            '@type' => ['Organization', 'TravelAgency'],
            '@id' => url('/').'#organization',
            'name' => (string) setting('site_name', config('app.name')),
            'url' => url('/'),
            'logo' => $image,
            'image' => $image,
            'description' => setting('meta_description'),
            'telephone' => setting('phone'),
            'email' => setting('email'),
            'address' => setting('address')
                ? ['@type' => 'PostalAddress', 'streetAddress' => (string) setting('address'), 'addressCountry' => 'PK']
                : null,
            'areaServed' => ['@type' => 'Country', 'name' => 'Pakistan'],
            'sameAs' => $sameAs,
        ]);
    }

    /** @return array<string, mixed> */
    public function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => (string) setting('site_name', config('app.name')),
            'publisher' => ['@id' => url('/').'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url('/search').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Trip + Product with Offer and rating. Needs the "approvedReviews" relation loaded.
     *
     * @param  list<string>  $images
     * @return array<string, mixed>
     */
    public function tour(Tour $tour, array $images = []): array
    {
        $url = route('tours.show', $tour->slug);

        $data = [
            '@context' => 'https://schema.org',
            '@type' => ['TouristTrip', 'Product'],
            '@id' => $url.'#trip',
            'name' => $tour->title,
            'description' => Str::limit(strip_tags((string) ($tour->short_description ?: $tour->description)), 300),
            'url' => $url,
            'image' => $images,
            'brand' => ['@type' => 'Organization', 'name' => (string) setting('site_name', config('app.name'))],
            'provider' => ['@id' => url('/').'#organization'],
            'offers' => [
                '@type' => 'Offer',
                'url' => $url,
                'price' => number_format((float) $tour->final_price_adult, 2, '.', ''),
                'priceCurrency' => (string) config('booking.currency', 'PKR'),
                'availability' => 'https://schema.org/InStock',
                'priceValidUntil' => now()->addYear()->toDateString(),
            ],
        ];

        if ((int) $tour->reviews_count_cached > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format((float) $tour->rating_cached, 1, '.', ''),
                'reviewCount' => (int) $tour->reviews_count_cached,
                'bestRating' => '5',
                'worstRating' => '1',
            ];

            $reviews = $tour->relationLoaded('approvedReviews') ? $tour->approvedReviews->take(5) : collect();

            $data['review'] = $reviews->map(fn ($r) => [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => $r->name],
                'datePublished' => $r->created_at?->toDateString(),
                'reviewBody' => $r->comment,
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $r->rating, 'bestRating' => 5, 'worstRating' => 1],
            ])->values()->all();
        }

        return $this->clean($data);
    }

    /**
     * @param  Collection<int, object{question: string, answer: string}>  $items
     * @return array<string, mixed>
     */
    public function faqPage(Collection $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $items->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
            ])->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function blogPosting(Blog $post): array
    {
        $url = route('blog.show', $post->slug);

        return $this->clean([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => Str::limit(strip_tags((string) ($post->excerpt ?: $post->content)), 200),
            'image' => $post->coverUrl('large'),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'author' => ['@type' => 'Organization', 'name' => (string) setting('site_name', config('app.name'))],
            'publisher' => ['@id' => url('/').'#organization'],
        ]);
    }

    /** @param array<string, mixed> $data */
    public function encode(array $data): string
    {
        return (string) json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_THROW_ON_ERROR
        );
    }

    private function siteImage(): ?string
    {
        foreach (['logo', 'default_og_image', 'hero_image'] as $key) {
            $path = setting($key);

            if (filled($path)) {
                return asset('storage/'.ltrim((string) $path, '/'));
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function clean(array $data): array
    {
        return array_filter($data, fn ($value) => ! in_array($value, [null, '', []], true));
    }
}