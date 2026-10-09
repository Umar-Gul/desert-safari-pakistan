<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Desert;
use App\Models\Facility;
use App\Models\Highlight;
use App\Models\Tour;
use App\Models\Vehicle;
use App\Support\CacheKeys;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        // Cleared by the catalogue observers whenever public content changes.
        $xml = Cache::remember(CacheKeys::SITEMAP, 86400, fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function build(): string
    {
        $urls = [];

        $add = function (string $loc, ?string $lastmod, string $freq, string $priority) use (&$urls): void {
            $urls[] = compact('loc', 'lastmod', 'freq', 'priority');
        };

        foreach ([
            [route('home'), 'daily', '1.0'],
            [route('tours.index'), 'daily', '0.9'],
            [route('events.index'), 'monthly', '0.6'],
            [route('blog.index'), 'weekly', '0.6'],
            [route('gallery.index'), 'monthly', '0.5'],
            [route('about'), 'yearly', '0.4'],
            [route('contact'), 'yearly', '0.5'],
            [route('terms'), 'yearly', '0.2'],
            [route('privacy'), 'yearly', '0.2'],
            [route('cookies'), 'yearly', '0.2'],
            [route('refund'), 'yearly', '0.3'],
        ] as [$loc, $freq, $priority]) {
            $add($loc, null, $freq, $priority);
        }

        $groups = [
            [Category::query()->active()->get(['slug', 'updated_at']), 'categories.show', 'weekly', '0.8'],
            [Tour::query()->active()->get(['slug', 'updated_at']), 'tours.show', 'weekly', '0.9'],
            [Desert::query()->active()->get(['slug', 'updated_at']), 'deserts.show', 'monthly', '0.6'],
            [CampType::query()->active()->get(['slug', 'updated_at']), 'camps.show', 'monthly', '0.5'],
            [Vehicle::query()->active()->get(['slug', 'updated_at']), 'vehicles.show', 'monthly', '0.5'],
            [Facility::query()->active()->get(['slug', 'updated_at']), 'facilities.show', 'monthly', '0.4'],
            [Highlight::query()->active()->get(['slug', 'updated_at']), 'highlights.show', 'monthly', '0.4'],
            [BlogCategory::query()->active()->get(['slug', 'updated_at']), 'blog.category', 'weekly', '0.4'],
            [Blog::query()->published()->get(['slug', 'updated_at']), 'blog.show', 'monthly', '0.6'],
        ];

        foreach ($groups as [$rows, $route, $freq, $priority]) {
            foreach ($rows as $row) {
                $add(route($route, $row->slug), $row->updated_at?->toDateString(), $freq, $priority);
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>'
                .($u['lastmod'] ? '<lastmod>'.$u['lastmod'].'</lastmod>' : '')
                .'<changefreq>'.$u['freq'].'</changefreq><priority>'.$u['priority'].'</priority></url>'."\n";
        }

        return $xml.'</urlset>'."\n";
    }
}