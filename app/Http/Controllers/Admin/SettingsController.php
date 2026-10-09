<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(string $group = 'general'): View
    {
        $groups = $this->groups();

        abort_unless(isset($groups[$group]), 404);

        return view('admin.settings.edit', [
            'groups' => $groups,
            'group' => $group,
            'values' => Setting::map(),
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        $groups = $this->groups();

        abort_unless(isset($groups[$group]), 404);

        $fields = $groups[$group]['fields'];

        $request->validate($this->rules($fields));

        foreach ($fields as $key => $field) {
            if ($field['type'] === 'image') {
                $this->saveImage($request, $key, $group);

                continue;
            }

            // A field missing from the request is left untouched.
            if (! $request->has($key)) {
                continue;
            }

            $value = (string) $request->input($key, '');

            if ($field['type'] === 'richtext') {
                $value = $this->cleanHtml($value);
            }

            $this->put($key, $group, $value);
        }

        Setting::flushCache();

        return redirect()
            ->route('admin.settings.edit', $group)
            ->with('success', 'Settings saved.');
    }

    /** @return array<string, array{label: string, fields: array<string, array<string, mixed>>}> */
    private function groups(): array
    {
        return config('admin_settings.groups', []);
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, array<int, mixed>>
     */
    private function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $key => $field) {
            if ($field['type'] === 'image') {
                $rules[$key] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
                $rules["remove_{$key}"] = ['nullable', 'boolean'];

                continue;
            }

            $rules[$key] = $field['rules'] ?? match ($field['type']) {
                'email' => ['nullable', 'email', 'max:190'],
                'url' => ['nullable', 'url', 'max:255'],
                'textarea' => ['nullable', 'string', 'max:'.($field['max'] ?? 2000)],
                'richtext' => ['nullable', 'string', 'max:20000'],
                default => ['nullable', 'string', 'max:'.($field['max'] ?? 255)],
            };

            if (! empty($field['required'])) {
                $rules[$key] = array_merge(['required'], array_diff($rules[$key], ['nullable']));
            }
        }

        return $rules;
    }

    private function saveImage(Request $request, string $key, string $group): void
    {
        // Only files this screen stored are ever deleted (value comes from the DB, not config defaults).
        $current = (string) Setting::query()->where('key', $key)->value('value');

        if ($request->hasFile($key)) {
            $path = $request->file($key)->store('settings', 'public');

            if ($current !== '') {
                Storage::disk('public')->delete($current);
            }

            $this->put($key, $group, $path);

            return;
        }

        if ($request->boolean("remove_{$key}") && $current !== '') {
            Storage::disk('public')->delete($current);
            $this->put($key, $group, '');
        }
    }

    private function put(string $key, string $group, string $value): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => $value, 'type' => 'string'],
        );
    }

    private function cleanHtml(string $html): string
    {
        // mews/purifier's clean() when available; otherwise a stricter tag whitelist.
        return function_exists('clean')
            ? (string) clean($html)
            : strip_tags($html, '<p><br><h2><h3><ul><ol><li><strong><em>');
    }
}