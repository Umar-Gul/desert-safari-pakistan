<x-admin.layout title="Questions">
    <h1 class="mb-6 text-2xl font-bold text-stone-900">Questions</h1>

    <div class="mb-4 flex flex-wrap gap-2 text-sm">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.questions.index', ['status' => $key]) }}"
               class="rounded-full px-4 py-1.5 {{ $filter === $key ? 'bg-amber-600 text-white' : 'bg-white text-stone-700 hover:bg-stone-50' }}">
                {{ $label }}@if ($key !== 'all') ({{ $counts[$key] ?? 0 }})@endif
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($questions as $question)
            <article class="rounded-lg bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-stone-900">{{ $question->name }} <span class="font-normal text-stone-500">&lt;{{ $question->email }}&gt;</span></p>
                        <p class="text-xs text-stone-500">{{ $question->tour?->title ?? 'General question' }} | {{ $question->created_at?->format('d M Y') }}</p>
                    </div>
                    <x-admin.badge :color="$question->status->color()" :label="$question->status->label()" />
                </div>

                <p class="mt-3 whitespace-pre-line text-sm text-stone-800">{{ $question->question }}</p>

                @can('update', $question)
                    <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="mt-4 space-y-3 text-sm">
                        @csrf @method('PUT')
                        <textarea name="answer" rows="3" placeholder="Write the answer shown on the site" class="block w-full rounded-md border-stone-300 text-sm">{{ old('answer', $question->answer) }}</textarea>
                        <div class="flex flex-wrap items-center gap-3">
                            <select name="status" class="rounded-md border-stone-300 text-sm">
                                @foreach (\App\Enums\ReviewStatus::cases() as $s)
                                    <option value="{{ $s->value }}" @selected($question->status === $s)>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-md bg-stone-800 px-4 py-1.5 font-medium text-white hover:bg-stone-700">Save</button>
                            @can('delete', $question)
                                <button type="button" x-data class="text-red-600 hover:underline"
                                        @click="$dispatch('confirm-delete', { action: '{{ route('admin.questions.destroy', $question) }}', name: 'this question' })">Delete</button>
                            @endcan
                        </div>
                        <p class="text-xs text-stone-500">A question needs an answer before it can be approved.</p>
                    </form>
                @elseif ($question->answer)
                    <p class="mt-3 rounded bg-stone-50 p-3 text-sm text-stone-700">{{ $question->answer }}</p>
                @endcan
            </article>
        @empty
            <div class="rounded-lg bg-white p-8 text-center text-sm text-stone-500 shadow-sm">No questions here.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>
</x-admin.layout>