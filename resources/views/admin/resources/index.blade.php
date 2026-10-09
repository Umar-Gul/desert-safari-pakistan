@php
    $canCreate = auth()->user()->can('create', $def['model']);
@endphp

<x-admin.layout :title="$def['plural']">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">{{ $def['plural'] }}</h1>

        @if ($canCreate)
            <a href="{{ route("admin.{$resource}.create") }}"
               class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                New {{ strtolower($def['singular']) }}
            </a>
        @endif
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-3">
        @if (! empty($def['search']))
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search..."
                   class="w-full max-w-xs rounded-md border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        @endif

        @if (! empty($def['toggle']))
            <select name="status" class="rounded-md border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Hidden</option>
            </select>
        @endif

        <button type="submit" class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 hover:bg-stone-50">Filter</button>

        @if (request()->hasAny(['q', 'status']))
            <a href="{{ route("admin.{$resource}.index") }}" class="text-sm text-stone-500 hover:text-amber-700">Clear</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-500">
                <tr>
                    @foreach ($def['columns'] as $col)
                        <th class="px-4 py-3">{{ $col['label'] }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-stone-100">
                @forelse ($rows as $row)
                    @php
                        $rowLabel = \Illuminate\Support\Str::limit((string) data_get($row, $def['label_key'] ?? 'name'), 50);
                    @endphp
                    <tr>
                        @foreach ($def['columns'] as $col)
                            @php
                                $type = $col['type'] ?? 'text';
                                $v = $type === 'image' ? null : data_get($row, $col['key']);
                            @endphp
                            <td class="px-4 py-3 align-middle">
                                @switch($type)
                                    @case('image')
                                        @if ($url = $row->coverUrl('thumb'))
                                            <img src="{{ $url }}" alt="" class="h-10 w-14 rounded object-cover">
                                        @else
                                            <span class="text-stone-300">-</span>
                                        @endif
                                        @break

                                    @case('bool')
                                        <x-admin.badge :color="$v ? 'green' : 'stone'" :label="$v ? 'Yes' : 'No'" />
                                        @break

                                    @case('enum')
                                        @if ($v)
                                            <x-admin.badge
                                                :color="$col['colors'][$v->value] ?? (method_exists($v, 'color') ? $v->color() : 'stone')"
                                                :label="$v->label()" />
                                        @endif
                                        @break

                                    @case('date')
                                        {{ $v ? $v->format('d M Y, H:i') : '-' }}
                                        @break

                                    @case('money')
                                        {{ $v === null ? '-' : number_format((float) $v) }}
                                        @break

                                    @default
                                        @php $text = \Illuminate\Support\Str::limit((string) $v, 60); @endphp
                                        {{ $text === '' ? '-' : $text }}
                                @endswitch
                            </td>
                        @endforeach

                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            @can('update', $row)
                                <a href="{{ route("admin.{$resource}.edit", $row->getKey()) }}" class="text-amber-700 hover:underline">Edit</a>

                                @if (! empty($def['toggle']))
                                    <form method="POST" action="{{ route("admin.{$resource}.toggle", $row->getKey()) }}" class="ml-3 inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-stone-600 hover:underline">{{ $row->is_active ? 'Hide' : 'Show' }}</button>
                                    </form>
                                @endif
                            @endcan

                            @can('delete', $row)
                                <button type="button" x-data
                                        @click="$dispatch('confirm-delete', { action: '{{ route("admin.{$resource}.destroy", $row->getKey()) }}', name: @js($rowLabel) })"
                                        class="ml-3 text-red-600 hover:underline">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($def['columns']) + 1 }}" class="px-4 py-10 text-center text-stone-500">
                            Nothing here yet.
                            @if ($canCreate)
                                <a href="{{ route("admin.{$resource}.create") }}" class="text-amber-700 hover:underline">Add the first one</a>.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rows->links() }}</div>
</x-admin.layout>