@php
    $isEdit = $record->exists;
    $action = $isEdit
        ? route("admin.{$resource}.update", $record->getKey())
        : route("admin.{$resource}.store");
    $section = null;
@endphp

<x-admin.layout :title="($isEdit ? 'Edit ' : 'New ').strtolower($def['singular'])">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">{{ $isEdit ? 'Edit' : 'New' }} {{ strtolower($def['singular']) }}</h1>
        <a href="{{ route("admin.{$resource}.index") }}" class="text-sm text-stone-500 hover:text-amber-700">&larr; Back to {{ strtolower($def['plural']) }}</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">Please fix the highlighted fields.</div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data"
          class="max-w-3xl space-y-6 rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        @foreach ($def['fields'] as $key => $field)
            @if (($field['section'] ?? null) !== $section)
                @php $section = $field['section'] ?? null; @endphp
                @if ($section)
                    <h2 class="border-t border-stone-100 pt-6 text-sm font-semibold uppercase tracking-wide text-stone-500">{{ $section }}</h2>
                @endif
            @endif

            <x-admin.field :name="$key" :field="$field" :record="$record" :options="$options[$key] ?? []" />
        @endforeach

        <div class="flex items-center justify-end gap-3 border-t border-stone-100 pt-4">
            <a href="{{ route("admin.{$resource}.index") }}" class="text-sm text-stone-600 hover:underline">Cancel</a>
            <x-primary-button>{{ $isEdit ? 'Save changes' : 'Create' }}</x-primary-button>
        </div>
    </form>
</x-admin.layout>