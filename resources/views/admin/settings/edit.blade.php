<x-admin.layout title="Settings">
    <h1 class="mb-6 text-2xl font-bold text-stone-900">Settings</h1>

    <div class="grid gap-6 lg:grid-cols-4">
        <nav class="h-fit rounded-lg bg-white p-2 shadow-sm lg:col-span-1">
            @foreach ($groups as $key => $g)
                <a href="{{ route('admin.settings.edit', $key) }}"
                   class="block rounded-md px-3 py-2 text-sm {{ $key === $group ? 'bg-amber-50 font-medium text-amber-800' : 'text-stone-700 hover:bg-stone-50' }}">
                    {{ $g['label'] }}
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.settings.update', $group) }}" enctype="multipart/form-data"
              class="space-y-6 rounded-lg bg-white p-6 shadow-sm lg:col-span-3">
            @csrf
            @method('PUT')

            <h2 class="text-lg font-semibold text-stone-900">{{ $groups[$group]['label'] }}</h2>

            @foreach ($groups[$group]['fields'] as $key => $field)
                @php $value = old($key, $values[$key] ?? ''); @endphp

                <div>
                    <x-input-label :for="$key" :value="$field['label'].(! empty($field['required']) ? ' *' : '')" />

                    @switch($field['type'])
                        @case('textarea')
                        @case('richtext')
                            <textarea id="{{ $key }}" name="{{ $key }}" rows="{{ $field['type'] === 'richtext' ? 10 : 3 }}"
                                      class="mt-1 block w-full rounded-md border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 {{ $field['type'] === 'richtext' ? 'font-mono text-sm' : '' }}">{{ $value }}</textarea>
                            @break

                        @case('image')
                            @php $stored = (string) ($values[$key] ?? ''); @endphp
                            @if ($stored !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($stored))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($stored) }}" alt="{{ $field['label'] }}"
                                     class="mt-2 h-24 rounded border border-stone-200 object-contain">
                                <label class="mt-2 flex items-center gap-2 text-sm text-stone-600">
                                    <input type="checkbox" name="remove_{{ $key }}" value="1" class="rounded border-stone-300 text-amber-600">
                                    Remove current image
                                </label>
                            @endif
                            <input type="file" id="{{ $key }}" name="{{ $key }}" accept="image/jpeg,image/png,image/webp"
                                   class="mt-2 block w-full text-sm text-stone-600">
                            @break

                        @default
                            <x-text-input :id="$key" :name="$key" :type="$field['type'] === 'text' ? 'text' : $field['type']"
                                          :value="$value" class="mt-1 block w-full" />
                    @endswitch

                    @if (! empty($field['hint']))
                        <p class="mt-1 text-xs text-stone-500">{{ $field['hint'] }}</p>
                    @endif

                    <x-input-error :messages="$errors->get($key)" class="mt-2" />
                </div>
            @endforeach

            <div class="flex justify-end border-t border-stone-100 pt-4">
                <x-primary-button>Save settings</x-primary-button>
            </div>
        </form>
    </div>
</x-admin.layout>