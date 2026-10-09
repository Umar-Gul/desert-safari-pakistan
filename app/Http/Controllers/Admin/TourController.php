<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\FaqScope;
use App\Enums\TourBadge;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TourRequest;
use App\Models\Addon;
use App\Models\CampType;
use App\Models\Category;
use App\Models\Desert;
use App\Models\Faq;
use App\Models\Tour;
use App\Models\TourAdditionalInfo;
use App\Models\TourExclusion;
use App\Models\TourHighlight;
use App\Models\TourInclusion;
use App\Models\TourItineraryItem;
use App\Models\Vehicle;
use App\Support\CacheKeys;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TourController extends Controller
{
    private const RELATIONS = [
        'highlights', 'itineraryItems', 'inclusions', 'exclusions', 'additionalInfos',
        'faqs', 'addons', 'campTypes', 'vehicles', 'categories', 'media',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Tour::class);

        $term = trim((string) $request->input('q'));

        $tours = Tour::query()
            ->with('category:id,name')
            ->when($term !== '', fn ($q) => $q->where('title', 'like', '%'.addcslashes($term, '%_\\').'%'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', (int) $request->input('category_id')))
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tours.index', [
            'tours' => $tours,
            'categories' => Category::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Tour::class);

        return $this->form(new Tour(['is_active' => true, 'price_label' => 'per person']));
    }

    public function edit(int $tour): View
    {
        $model = Tour::query()->with(self::RELATIONS)->findOrFail($tour);

        Gate::authorize('update', $model);

        return $this->form($model);
    }

    public function store(TourRequest $request): RedirectResponse
    {
        Gate::authorize('create', Tour::class);

        $tour = $this->persist(new Tour(), $request);

        return redirect()->route('admin.tours.edit', $tour->id)->with('success', 'Tour created.');
    }

    public function update(TourRequest $request, int $tour): RedirectResponse
    {
        $model = Tour::query()->findOrFail($tour);

        Gate::authorize('update', $model);

        $this->persist($model, $request);

        return redirect()->route('admin.tours.edit', $model->id)->with('success', 'Tour saved.');
    }

    public function destroy(int $tour): RedirectResponse
    {
        $model = Tour::query()->findOrFail($tour);

        Gate::authorize('delete', $model);

        $model->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted.');
    }

    public function toggle(int $tour): RedirectResponse
    {
        $model = Tour::query()->findOrFail($tour);

        Gate::authorize('update', $model);

        $model->update(['is_active' => ! $model->is_active]);

        return back()->with('success', $model->is_active ? 'Tour is now active.' : 'Tour is now hidden.');
    }

    private function form(Tour $tour): View
    {
        $exists = $tour->exists;

        $state = [
            'highlights' => $exists ? $tour->highlights->map(fn ($r) => ['text' => $r->text, 'icon' => $r->icon])->values()->all() : [],
            'itinerary' => $exists ? $tour->itineraryItems->map(fn ($r) => ['time_label' => $r->time_label, 'title' => $r->title, 'description' => $r->description])->values()->all() : [],
            'inclusions' => $exists ? $tour->inclusions->map(fn ($r) => ['text' => $r->text])->values()->all() : [],
            'exclusions' => $exists ? $tour->exclusions->map(fn ($r) => ['text' => $r->text])->values()->all() : [],
            'additional_infos' => $exists ? $tour->additionalInfos->map(fn ($r) => ['title' => $r->title, 'content' => $r->content])->values()->all() : [],
            'faqs' => $exists ? $tour->faqs->map(fn ($r) => ['question' => $r->question, 'answer' => $r->answer])->values()->all() : [],
        ];

        return view('admin.tours.form', [
            'tour' => $tour,
            'state' => $state,
            'categories' => Category::query()->ordered()->get(['id', 'name']),
            'deserts' => Desert::query()->ordered()->get(['id', 'name']),
            'addons' => Addon::query()->ordered()->get(['id', 'name', 'price']),
            'campTypes' => CampType::query()->ordered()->get(['id', 'name']),
            'vehicles' => Vehicle::query()->ordered()->get(['id', 'name']),
        ]);
    }

    private function persist(Tour $tour, TourRequest $request): Tour
    {
        $v = $request->validated();

        // Media calls read the "media" relation; load it up front (lazy loading is blocked in dev).
        if ($tour->exists) {
            $tour->load('media');
        }

        DB::transaction(function () use ($tour, $request, $v) {
            $tour->fill([
                'category_id' => $v['category_id'],
                'desert_id' => $v['desert_id'] ?? null,
                'title' => $v['title'],
                'short_description' => $v['short_description'] ?? null,
                'description' => $this->sanitize($v['description'] ?? null),
                'duration_hours' => $v['duration_hours'] ?? 0,
                'base_price_adult' => $v['base_price_adult'],
                'base_price_child' => $v['base_price_child'] ?? 0,
                'discount_percent' => $v['discount_percent'] ?? 0,
                'price_label' => $v['price_label'],
                'has_dinner' => $request->boolean('has_dinner'),
                'has_breakfast' => $request->boolean('has_breakfast'),
                'has_guide' => $request->boolean('has_guide'),
                'has_transfer' => $request->boolean('has_transfer'),
                'has_itinerary' => $request->boolean('has_itinerary'),
                'max_group_size' => $v['max_group_size'] ?? null,
                'min_age' => $v['min_age'] ?? null,
                'badges' => array_map(fn (string $b) => TourBadge::from($b), $v['badges'] ?? []),
                'cancellation_policy' => $this->sanitize($v['cancellation_policy'] ?? null),
                'meta_title' => $v['meta_title'] ?? null,
                'meta_description' => $v['meta_description'] ?? null,
                'canonical' => $v['canonical'] ?? null,
                'og_image' => $v['og_image'] ?? null,
                'is_featured' => $request->boolean('is_featured'),
                'is_best_selling' => $request->boolean('is_best_selling'),
                'is_active' => $request->boolean('is_active'),
                'sort_order' => $v['sort_order'] ?? 0,
            ]);

            // Blank slug: generated on create (HasSlug), unchanged on update.
            if (filled($v['slug'] ?? null)) {
                $tour->slug = $v['slug'];
            }

            $tour->save();

            $this->syncLists($tour, $v);
            $this->syncOptions($tour, $v);
            $this->syncMedia($tour, $request);
        });

        CacheKeys::forgetCatalogue();

        return $tour;
    }

    /** @param array<string, mixed> $v */
    private function syncLists(Tour $tour, array $v): void
    {
        $id = $tour->id;

        TourHighlight::where('tour_id', $id)->delete();
        $rows = [];
        foreach ($this->filled($v['highlights'] ?? [], 'text') as $i => $r) {
            $rows[] = ['tour_id' => $id, 'text' => trim($r['text']), 'icon' => $r['icon'] ?? null, 'sort_order' => $i];
        }
        TourHighlight::insert($rows);

        TourItineraryItem::where('tour_id', $id)->delete();
        $rows = [];
        foreach ($this->filled($v['itinerary'] ?? [], 'title') as $i => $r) {
            $rows[] = [
                'tour_id' => $id,
                'time_label' => $r['time_label'] ?? null,
                'title' => trim($r['title']),
                'description' => $r['description'] ?? null,
                'sort_order' => $i,
            ];
        }
        TourItineraryItem::insert($rows);

        TourInclusion::where('tour_id', $id)->delete();
        $rows = [];
        foreach ($this->filled($v['inclusions'] ?? [], 'text') as $i => $r) {
            $rows[] = ['tour_id' => $id, 'text' => trim($r['text']), 'sort_order' => $i];
        }
        TourInclusion::insert($rows);

        TourExclusion::where('tour_id', $id)->delete();
        $rows = [];
        foreach ($this->filled($v['exclusions'] ?? [], 'text') as $i => $r) {
            $rows[] = ['tour_id' => $id, 'text' => trim($r['text']), 'sort_order' => $i];
        }
        TourExclusion::insert($rows);

        TourAdditionalInfo::where('tour_id', $id)->delete();
        $rows = [];
        foreach ($this->filled($v['additional_infos'] ?? [], 'title') as $i => $r) {
            $rows[] = ['tour_id' => $id, 'title' => trim($r['title']), 'content' => $r['content'] ?? null, 'sort_order' => $i];
        }
        TourAdditionalInfo::insert($rows);

        Faq::where('tour_id', $id)->delete();
        foreach ($this->filled($v['faqs'] ?? [], 'question') as $i => $r) {
            Faq::create([
                'scope' => FaqScope::Tour,
                'tour_id' => $id,
                'question' => trim($r['question']),
                'answer' => (string) ($r['answer'] ?? ''),
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }

    /** @param array<string, mixed> $v */
    private function syncOptions(Tour $tour, array $v): void
    {
        $extra = array_map('intval', $v['category_ids'] ?? []);
        $tour->categories()->sync(array_values(array_unique([...$extra, (int) $v['category_id']])));

        $tour->campTypes()->sync(array_map('intval', $v['camp_type_ids'] ?? []));
        $tour->vehicles()->sync(array_map('intval', $v['vehicle_ids'] ?? []));

        $addons = [];
        foreach ($v['addon_ids'] ?? [] as $addonId) {
            $price = $v['addon_prices'][$addonId] ?? null;
            $addons[(int) $addonId] = ['price_override' => filled($price) ? $price : null];
        }
        $tour->addons()->sync($addons);
    }

    private function syncMedia(Tour $tour, TourRequest $request): void
    {
        $alt = $request->input('cover_alt');

        if ($request->boolean('remove_cover') && ! $request->hasFile('cover')) {
            $tour->clearMediaCollection('cover');
        }

        if ($request->hasFile('cover')) {
            $tour->addMediaFromRequest('cover')
                ->withCustomProperties(['alt' => $alt])
                ->toMediaCollection('cover');
        } elseif (filled($alt) && ($media = $tour->getFirstMedia('cover'))) {
            $media->setCustomProperty('alt', $alt);
            $media->save();
        }

        $remove = array_map('intval', (array) $request->input('remove_gallery', []));

        if ($remove !== []) {
            $tour->media()
                ->where('collection_name', 'gallery')
                ->whereIn('id', $remove)
                ->get()
                ->each->delete();
        }

        foreach ((array) $request->file('gallery', []) as $file) {
            $tour->addMedia($file)->toMediaCollection('gallery');
        }
    }

    /**
     * Rows whose $key is not blank, re-indexed.
     *
     * @param  array<int|string, array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function filled(array $rows, string $key): array
    {
        return array_values(array_filter($rows, fn ($r) => is_array($r) && filled($r[$key] ?? null)));
    }

    private function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '' || $html === '<p><br></p>') {
            return null;
        }

        return function_exists('clean')
            ? (string) clean($html)
            : strip_tags($html, '<p><br><h2><h3><ul><ol><li><strong><em><u><a>');
    }
}