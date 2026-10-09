<x-admin.layout title="Tours">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">Tours</h1>
        @can('create', \App\Models\Tour::class)
            <a href="{{ route('admin.tours.create') }}" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">+ New tour</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow-sm">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title" class="rounded-md border-stone-300 text-sm">
        <select name="category_id" class="rounded-md border-stone-300 text-sm">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border-stone-300 text-sm">
            <option value="">Any status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Hidden</option>
        </select>
        <button class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-4 py-3">Tour</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Adult price</th>
                    <th class="px-4 py-3">Rating</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($tours as $tour)
                    <tr>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.tours.edit', $tour->id) }}" class="font-medium text-stone-900 hover:text-amber-700">{{ $tour->title }}</a>
                            @if ($tour->is_featured)<span class="ml-1 text-xs text-amber-700">Featured</span>@endif
                        </td>
                        <td class="px-4 py-3">{{ $tour->category?->name }}</td>
                        <td class="px-4 py-3">
                            {{ number_format($tour->final_price_adult) }}
                            @if ($tour->has_discount)
                                <span class="text-xs text-stone-400 line-through">{{ number_format((float) $tour->base_price_adult) }}</span>
                                <span class="text-xs text-green-700">-{{ $tour->discount_percent }}%</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $tour->reviews_count_cached ? number_format((float) $tour->rating_cached, 1).' ('.$tour->reviews_count_cached.')' : '-' }}</td>
                        <td class="px-4 py-3">
                            @can('update', $tour)
                                <form method="POST" action="{{ route('admin.tours.toggle', $tour->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"><x-admin.badge :color="$tour->is_active ? 'green' : 'stone'" :label="$tour->is_active ? 'Active' : 'Hidden'" /></button>
                                </form>
                            @else
                                <x-admin.badge :color="$tour->is_active ? 'green' : 'stone'" :label="$tour->is_active ? 'Active' : 'Hidden'" />
                            @endcan
                        </td>
                        <td class="space-x-3 px-4 py-3 text-right">
                            @can('update', $tour)
                                <a href="{{ route('admin.tours.edit', $tour->id) }}" class="text-amber-700 hover:underline">Edit</a>
                            @endcan
                            @can('delete', $tour)
                                <button type="button" x-data class="text-red-600 hover:underline"
                                        @click="$dispatch('confirm-delete', { action: '{{ route('admin.tours.destroy', $tour->id) }}', name: @js($tour->title) })">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-stone-500">No tours found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tours->links() }}</div>
</x-admin.layout>