<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CampType;
use App\Models\Desert;
use App\Models\Facility;
use App\Models\Highlight;
use App\Models\Tour;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** Desert, camp, vehicle, facility and highlight detail pages share one template. */
class InfoPageController extends Controller
{
    public function desert(string $slug): View
    {
        $desert = Desert::query()->active()->where('slug', $slug)->with('media')->firstOrFail();

        $tours = Tour::query()->active()->withCardRelations()
            ->where('desert_id', $desert->id)->ordered()->limit(12)->get();

        return $this->page('Deserts', $desert, $tours, 'Tours in this desert', array_filter(['Location' => $desert->location]));
    }

    public function camp(string $slug): View
    {
        $camp = CampType::query()->active()->where('slug', $slug)->with('media')->firstOrFail();

        $tours = $camp->tours()->active()->withCardRelations()->ordered()->limit(12)->get();

        return $this->page('Camps', $camp, $tours, 'Tours with this camp');
    }

    public function vehicle(string $slug): View
    {
        $vehicle = Vehicle::query()->active()->where('slug', $slug)->with('media')->firstOrFail();

        $tours = $vehicle->tours()->active()->withCardRelations()->ordered()->limit(12)->get();

        return $this->page('Vehicles', $vehicle, $tours, 'Tours using this vehicle', array_filter([
            'Seats' => $vehicle->capacity ? (string) $vehicle->capacity : null,
        ]));
    }

    public function facility(string $slug): View
    {
        $facility = Facility::query()->active()->where('slug', $slug)->with('media')->firstOrFail();

        return $this->page('Facilities', $facility, $this->featured(), 'Popular tours');
    }

    public function highlight(string $slug): View
    {
        $highlight = Highlight::query()->active()->where('slug', $slug)->with('media')->firstOrFail();

        return $this->page('Highlights', $highlight, $this->featured(), 'Popular tours');
    }

    /** @param array<string, string> $facts */
    private function page(string $kind, Model $item, Collection $tours, string $toursHeading, array $facts = []): View
    {
        return view('frontend.catalogue.show', [
            'kind' => $kind,
            'item' => $item,
            'name' => (string) ($item->name ?? $item->title),
            'tours' => $tours,
            'toursHeading' => $toursHeading,
            'facts' => $facts,
        ]);
    }

    private function featured(): Collection
    {
        return Tour::query()->active()->withCardRelations()->featured()->ordered()->limit(4)->get();
    }
}