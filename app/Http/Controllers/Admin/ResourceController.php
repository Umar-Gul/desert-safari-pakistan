<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * One controller for every simple CRUD module. Behaviour comes from config/admin_resources.php.
 * "resource" and "record" are read from the route by name (Laravel passes route
 * parameters to controller methods by position, which is easy to get wrong).
 */
class ResourceController extends Controller
{
    public function index(Request $request): View
    {
        $resource = $this->resource($request);
        $def = $this->definition($resource);
        $class = $def['model'];

        Gate::authorize('viewAny', $class);

        $query = $class::query();

        if (! empty($def['with'])) {
            $query->with($def['with']);
        }

        if (! empty($def['cover'])) {
            $query->with('media');
        }

        foreach ($def['counts'] ?? [] as $relation) {
            $query->withCount($relation);
        }

        $term = trim((string) $request->query('q', ''));

        if ($term !== '' && ! empty($def['search'])) {
            $like = '%'.addcslashes($term, '%_\\').'%';

            $query->where(function ($where) use ($def, $like) {
                foreach (array_values($def['search']) as $i => $column) {
                    $i === 0 ? $where->where($column, 'like', $like) : $where->orWhere($column, 'like', $like);
                }
            });
        }

        if (! empty($def['toggle'])) {
            match ($request->query('status')) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        foreach ($def['order'] ?? [['sort_order', 'asc'], ['id', 'asc']] as [$column, $direction]) {
            $query->orderBy($column, $direction);
        }

        return view('admin.resources.index', [
            'def' => $def,
            'resource' => $resource,
            'rows' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $resource = $this->resource($request);
        $def = $this->definition($resource);

        Gate::authorize('create', $def['model']);

        return $this->form($def, $resource, new $def['model']);
    }

    public function store(Request $request): RedirectResponse
    {
        $resource = $this->resource($request);
        $def = $this->definition($resource);

        Gate::authorize('create', $def['model']);

        $this->persist($request, $def, $resource, null);

        return redirect()
            ->route("admin.{$resource}.index")
            ->with('success', $def['singular'].' created.');
    }

    public function edit(Request $request): View
    {
        $resource = $this->resource($request);
        $def = $this->definition($resource);
        $model = $this->find($def, $this->recordId($request));

        Gate::authorize('update', $model);

        return $this->form($def, $resource, $model);
    }

    public function update(Request $request): RedirectResponse
    {
        $resource = $this->resource($request);
        $def = $this->definition($resource);
        $model = $this->find($def, $this->recordId($request));

        Gate::authorize('update', $model);

        $this->persist($request, $def, $resource, $model);

        return redirect()
            ->route("admin.{$resource}.index")
            ->with('success', $def['singular'].' saved.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $resource = $this->resource($request);
        $def = $this->definition($resource);
        $model = $this->find($def, $this->recordId($request));

        Gate::authorize('delete', $model);

        foreach ($def['block_delete'] ?? [] as $relation => $noun) {
            $count = $model->{$relation}()->count();

            if ($count > 0) {
                return back()->with(
                    'error',
                    'This '.strtolower($def['singular'])." is used by {$count} {$noun}. Move or remove them first."
                );
            }
        }

        $model->delete();

        return redirect()
            ->route("admin.{$resource}.index")
            ->with('success', $def['singular'].' deleted.');
    }

    public function toggle(Request $request): RedirectResponse
    {
        $resource = $this->resource($request);
        $def = $this->definition($resource);
        $model = $this->find($def, $this->recordId($request));

        Gate::authorize('update', $model);

        $model->update(['is_active' => ! $model->is_active]);

        return back()->with('success', $def['singular'].($model->is_active ? ' is now visible.' : ' is now hidden.'));
    }

    // ---------------------------------------------------------------------

    private function resource(Request $request): string
    {
        return (string) $request->route('resource');
    }

    private function recordId(Request $request): string
    {
        return (string) $request->route('record');
    }

    /** @return array<string, mixed> */
    private function definition(string $resource): array
    {
        $def = config("admin_resources.{$resource}");

        abort_unless(is_array($def), 404);

        return $def;
    }

    private function find(array $def, string $id): Model
    {
        return $def['model']::query()->findOrFail($id);
    }

    private function form(array $def, string $resource, Model $record): View
    {
        return view('admin.resources.form', [
            'def' => $def,
            'resource' => $resource,
            'record' => $record,
            'options' => $this->options($def),
        ]);
    }

    /** @return array<string, array<int|string, string>> */
    private function options(array $def): array
    {
        $options = [];

        foreach ($def['fields'] as $key => $field) {
            if ($field['type'] !== 'select') {
                continue;
            }

            if (! empty($field['options'])) {
                $options[$key] = $field['options']::options();
            } elseif (! empty($field['options_from'])) {
                $source = $field['options_from'];
                $options[$key] = $source['model']::query()
                    ->orderBy($source['label'])
                    ->pluck($source['label'], 'id')
                    ->all();
            }
        }

        return $options;
    }

    private function persist(Request $request, array $def, string $resource, ?Model $record): Model
    {
        $data = $this->validatedData($request, $def, $resource, $record);
        $creating = $record === null;

        $record ??= new $def['model'];
        $record->fill($data);

        if ($creating && $resource === 'blogs') {
            $record->author_id = $request->user()->id;
        }

        $record->save();

        $this->saveCover($request, $def, $record);

        return $record;
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, array $def, string $resource, ?Model $record): array
    {
        foreach ($def['fields'] as $key => $field) {
            if (! empty($field['uppercase']) && $request->has($key)) {
                $request->merge([$key => strtoupper(trim((string) $request->input($key)))]);
            }
        }

        $validated = $request->validate($this->rules($def, $record));

        $data = [];

        foreach ($def['fields'] as $key => $field) {
            $type = $field['type'];

            if ($type === 'image') {
                continue;
            }

            if ($type === 'checkbox') {
                $data[$key] = $request->boolean($key);

                continue;
            }

            $value = $validated[$key] ?? null;

            $data[$key] = match ($type) {
                'richtext' => $value === null ? null : $this->cleanHtml((string) $value),
                'lines' => array_values(array_filter(
                    array_map('trim', preg_split('/\R/', (string) $value) ?: []),
                    fn (string $line) => $line !== ''
                )),
                'number' => ($value === null || $value === '') ? ($field['default'] ?? null) : $value + 0,
                default => $value === '' ? null : $value,
            };
        }

        // A blank slug keeps the current URL (edit) or is generated by the model (create).
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        return $this->normalize($resource, $data);
    }

    /** Rules that depend on more than one field. */
    private function normalize(string $resource, array $data): array
    {
        if ($resource === 'faqs' && ($data['scope'] ?? null) === 'global') {
            $data['tour_id'] = null;
        }

        if ($resource === 'blogs' && ($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(array $def, ?Model $record): array
    {
        $table = (new $def['model'])->getTable();
        $ignoreId = $record?->getKey();
        $rules = [];

        foreach ($def['fields'] as $key => $field) {
            $base = match ($field['type']) {
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'checkbox' => ['nullable', 'boolean'],
                'number' => array_values(array_filter([
                    'nullable', 'numeric', 'min:0', ! empty($field['integer']) ? 'integer' : null,
                ])),
                'select' => $this->selectRules($field),
                'date', 'datetime' => ['nullable', 'date'],
                'richtext' => ['nullable', 'string', 'max:50000'],
                'lines' => ['nullable', 'string', 'max:3000'],
                'slug' => ['nullable', 'alpha_dash', 'max:190', Rule::unique($table, 'slug')->ignore($ignoreId)],
                'textarea' => ['nullable', 'string', 'max:'.($field['max'] ?? 2000)],
                default => ['nullable', 'string', 'max:'.($field['max'] ?? 255)],
            };

            if (! empty($field['unique'])) {
                $base[] = Rule::unique($table, $key)->ignore($ignoreId);
            }

            if (! empty($field['required'])) {
                $base = array_merge(['required'], array_values(array_filter($base, fn ($rule) => $rule !== 'nullable')));
            }

            $rules[$key] = $field['rules'] ?? $base;
        }

        if (! empty($def['cover'])) {
            $rules['cover_alt'] = ['nullable', 'string', 'max:160'];
            $rules['remove_cover'] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    /** @return array<int, mixed> */
    private function selectRules(array $field): array
    {
        if (! empty($field['options'])) {
            return ['nullable', Rule::in(array_keys($field['options']::options()))];
        }

        $model = $field['options_from']['model'];

        return ['nullable', 'integer', Rule::exists((new $model)->getTable(), 'id')];
    }

    private function saveCover(Request $request, array $def, Model $record): void
    {
        if (empty($def['cover'])) {
            return;
        }

        $alt = (string) $request->input('cover_alt', '');

        if ($request->boolean('remove_cover')) {
            $record->clearMediaCollection('cover');
        }

        if ($request->hasFile('cover')) {
            // The cover collection is single-file, so this replaces the old image.
            $record->addMediaFromRequest('cover')
                ->withCustomProperties(['alt' => $alt])
                ->toMediaCollection('cover');

            return;
        }

        if ($request->has('cover_alt') && ($media = $record->getFirstMedia('cover'))) {
            $media->setCustomProperty('alt', $alt);
            $media->save();
        }
    }

    private function cleanHtml(string $html): string
    {
        // mews/purifier's clean() when available; otherwise a strict tag whitelist.
        return function_exists('clean')
            ? (string) clean($html)
            : strip_tags($html, '<p><br><h2><h3><ul><ol><li><strong><em><a><img>');
    }
}