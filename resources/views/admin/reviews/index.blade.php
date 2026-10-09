<x-admin.layout title="Reviews">
    <h1 class="mb-6 text-2xl font-bold text-stone-900">Reviews</h1>

    <div class="mb-4 flex flex-wrap gap-2 text-sm">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.reviews.index', ['status' => $key]) }}"
               class="rounded-full px-4 py-1.5 {{ $filter === $key ? 'bg-amber-600 text-white' : 'bg-white text-stone-700 hover:bg-stone-50' }}">
                {{ $label }}@if ($key !== 'all') ({{ $counts[$key] ?? 0 }})@endif
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <article class="rounded-lg bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-stone-900">{{ $review->name }}<span class="font-normal text-stone-500">{{ $review->country ? ', '.$review->country : '' }}</span></p>
                        <p class="text-sm text-amber-600">{{ str_repeat('★', $review->rating) }}<span class="text-stone-300">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                        <p class="text-xs text-stone-500">{{ $review->tour?->title }}{{ $review->visited_on ? ' | visited '.$review->visited_on->format('M Y') : '' }}</p>
                    </div>
                    <x-admin.badge :color="$review->status->color()" :label="$review->status->label()" />
                </div>

                @if ($review->title)<p class="mt-3 font-medium text-stone-800">{{ $review->title }}</p>@endif
                <p class="mt-1 whitespace-pre-line text-sm text-stone-700">{{ $review->comment }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                    @can('update', $review)
                        @foreach (['approved' => ['Approve', 'bg-green-600 hover:bg-green-700'], 'rejected' => ['Reject', 'bg-stone-600 hover:bg-stone-700']] as $value => [$label, $class])
                            @if ($review->status->value !== $value)
                                <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $value }}">
                                    <button class="rounded-md px-3 py-1.5 font-medium text-white {{ $class }}">{{ $label }}</button>
                                </form>
                            @endif
                        @endforeach
                    @endcan
                    @can('delete', $review)
                        <button type="button" x-data class="px-2 text-red-600 hover:underline"
                                @click="$dispatch('confirm-delete', { action: '{{ route('admin.reviews.destroy', $review) }}', name: 'this review' })">Delete</button>
                    @endcan
                </div>
            </article>
        @empty
            <div class="rounded-lg bg-white p-8 text-center text-sm text-stone-500 shadow-sm">No reviews here.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
</x-admin.layout>