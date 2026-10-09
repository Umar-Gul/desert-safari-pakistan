@props(['name', 'field', 'record', 'options' => []])

@php
    $type = $field['type'];
    $required = ! empty($field['required']);

    $current = null;
    if ($type !== 'image') {
        $current = $record->exists ? data_get($record, $name) : ($field['default'] ?? null);

        if ($current instanceof \BackedEnum) {
            $current = $current->value;
        }
        if ($current instanceof \Carbon\CarbonInterface) {
            $current = $current->format($type === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i');
        }
        if (is_array($current)) {
            $current = implode("\n", $current);
        }
    }

    $value = $type === 'image' ? null : old($name, $current);
    $inputClass = 'mt-1 block w-full rounded-md border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500';
@endphp

<div>
    @if ($type === 'checkbox')
        {{-- Hidden 0 makes an unchecked box survive validation errors (old input). --}}
        <input type="hidden" name="{{ $name }}" value="0">
        <label class="flex items-center gap-2 text-sm text-stone-700">
            <input type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value)
                   class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
            {{ $field['label'] }}
        </label>
    @else
        <x-input-label :for="$name" :value="$field['label'].($required ? ' *' : '')" />

        @switch($type)
            @case('textarea')
            @case('lines')
                <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $field['rows'] ?? ($type === 'lines' ? 5 : 4) }}"
                          class="{{ $inputClass }}">{{ $value }}</textarea>
                @break

            @case('richtext')
                <textarea id="{{ $name }}" name="{{ $name }}" rows="14"
                          class="{{ $inputClass }} font-mono text-sm">{{ $value }}</textarea>
                @break

            @case('select')
                <select id="{{ $name }}" name="{{ $name }}" class="{{ $inputClass }}">
                    @if (! $required || isset($field['blank']))
                        <option value="">{{ $field['blank'] ?? 'None' }}</option>
                    @endif
                    @foreach ($options as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                @break

            @case('number')
                <input type="number" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" min="0"
                       step="{{ ! empty($field['integer']) ? 1 : 'any' }}" class="{{ $inputClass }}">
                @break

            @case('date')
                <input type="date" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" class="{{ $inputClass }}">
                @break

            @case('datetime')
                <input type="datetime-local" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" class="{{ $inputClass }}">
                @break

            @case('image')
                @php $media = $record->exists ? $record->getFirstMedia('cover') : null; @endphp

                @if ($media)
                    <img src="{{ $record->coverUrl('thumb') ?? $media->getUrl() }}" alt=""
                         class="mt-2 h-24 rounded border border-stone-200 object-cover">
                    <label class="mt-2 flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remove_cover" value="1" class="rounded border-stone-300 text-amber-600">
                        Remove current image
                    </label>
                @endif

                <input type="file" id="cover" name="cover" accept="image/jpeg,image/png,image/webp"
                       class="mt-2 block w-full text-sm text-stone-600">
                <input type="text" id="cover_alt" name="cover_alt" maxlength="160"
                       value="{{ old('cover_alt', $media?->getCustomProperty('alt')) }}"
                       placeholder="Image description (alt text, helps search engines and screen readers)"
                       class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('cover_alt')" class="mt-2" />
                @break

            @default
                <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}"
                       @if (isset($field['max'])) maxlength="{{ $field['max'] }}" @endif
                       class="{{ $inputClass }}">
        @endswitch
    @endif

    @if (! empty($field['hint']))
        <p class="mt-1 text-xs text-stone-500">{{ $field['hint'] }}</p>
    @endif

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>