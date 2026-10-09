@php
    $colors = ['new' => 'amber', 'read' => 'blue', 'replied' => 'green'];
@endphp

<x-admin.layout title="Enquiries">
    <h1 class="mb-6 text-2xl font-bold text-stone-900">Enquiries</h1>

    <div class="mb-4 flex flex-wrap gap-2 text-sm">
        @foreach (['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.enquiries.index', ['status' => $key]) }}"
               class="rounded-full px-4 py-1.5 {{ $filter === $key ? 'bg-amber-600 text-white' : 'bg-white text-stone-700 hover:bg-stone-50' }}">
                {{ $label }}@if ($key !== 'all') ({{ $counts[$key] ?? 0 }})@endif
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($enquiries as $enquiry)
            <article class="rounded-lg bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-stone-900">{{ $enquiry->subject }}</p>
                        <p class="text-sm text-stone-600">
                            {{ $enquiry->name }} |
                            <a href="mailto:{{ $enquiry->email }}" class="text-amber-700">{{ $enquiry->email }}</a>
                            @if ($enquiry->phone) | {{ $enquiry->phone }}@endif
                        </p>
                        <p class="text-xs text-stone-500">{{ $enquiry->created_at?->format('d M Y H:i') }}</p>
                    </div>
                    <x-admin.badge :color="$colors[$enquiry->status->value]" :label="$enquiry->status->label()" />
                </div>

                <p class="mt-3 whitespace-pre-line text-sm text-stone-800">{{ $enquiry->message }}</p>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                    @can('update', $enquiry)
                        @foreach (['read' => 'Mark read', 'replied' => 'Mark replied'] as $value => $label)
                            @if ($enquiry->status->value !== $value)
                                <form method="POST" action="{{ route('admin.enquiries.status', $enquiry) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $value }}">
                                    <button class="rounded-md bg-stone-800 px-3 py-1.5 font-medium text-white hover:bg-stone-700">{{ $label }}</button>
                                </form>
                            @endif
                        @endforeach
                    @endcan
                    @can('delete', $enquiry)
                        <button type="button" x-data class="px-2 text-red-600 hover:underline"
                                @click="$dispatch('confirm-delete', { action: '{{ route('admin.enquiries.destroy', $enquiry) }}', name: 'this enquiry' })">Delete</button>
                    @endcan
                </div>
            </article>
        @empty
            <div class="rounded-lg bg-white p-8 text-center text-sm text-stone-500 shadow-sm">No enquiries here.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $enquiries->links() }}</div>
</x-admin.layout>