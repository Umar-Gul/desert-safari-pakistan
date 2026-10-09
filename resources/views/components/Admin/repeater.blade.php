@props(['name', 'rows' => [], 'fields', 'addLabel' => 'Add row'])

@php
    $blank = collect($fields)->mapWithKeys(fn ($f) => [$f['key'] => ''])->all();
@endphp

<div x-data="{ rows: @js(array_values($rows)), blank: @js($blank) }" class="space-y-3">
    <template x-for="(row, i) in rows" :key="i">
        <div class="flex flex-wrap items-start gap-2 rounded-md border border-stone-200 bg-stone-50 p-3">
            @foreach ($fields as $f)
                <div class="{{ $f['class'] ?? 'min-w-[10rem] flex-1' }}">
                    @if (($f['type'] ?? 'text') === 'textarea')
                        <textarea :name="`{{ $name }}[${i}][{{ $f['key'] }}]`" x-model="row.{{ $f['key'] }}" rows="2"
                                  placeholder="{{ $f['label'] }}"
                                  class="block w-full rounded-md border-stone-300 text-sm"></textarea>
                    @else
                        <input type="text" :name="`{{ $name }}[${i}][{{ $f['key'] }}]`" x-model="row.{{ $f['key'] }}"
                               placeholder="{{ $f['label'] }}"
                               class="block w-full rounded-md border-stone-300 text-sm">
                    @endif
                </div>
            @endforeach
            <button type="button" @click="rows.splice(i, 1)" class="px-2 py-2 text-sm text-red-600 hover:underline">Remove</button>
        </div>
    </template>

    <button type="button" @click="rows.push({ ...blank })" class="text-sm font-medium text-amber-700 hover:underline">+ {{ $addLabel }}</button>
</div>