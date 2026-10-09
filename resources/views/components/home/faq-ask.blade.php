@props(['faqs', 'tours'])

<section class="mx-auto mt-16 max-w-7xl px-4" id="faq">
    <h2 class="mb-6 text-2xl font-extrabold text-stone-900 sm:text-3xl">Questions? We have answers</h2>

    <x-flash />

    <div class="grid gap-8 lg:grid-cols-2">
        <div>
            @if ($faqs->isNotEmpty())
                <x-faq-accordion :items="$faqs" />
            @else
                <p class="text-stone-600">Our FAQ is coming soon. Ask us anything using the form.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('questions.store') }}" class="space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            @csrf
            <h3 class="text-lg font-bold text-stone-900">Ask a question</h3>
            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="field-label" for="q_name">Your name</label>
                    <input id="q_name" name="name" type="text" value="{{ old('name') }}" required class="field">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label" for="q_email">Email (not shown)</label>
                    <input id="q_email" name="email" type="email" value="{{ old('email') }}" required class="field">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="field-label" for="q_tour">About a tour (optional)</label>
                <select id="q_tour" name="tour_id" class="field">
                    <option value="">General question</option>
                    @foreach ($tours as $tour)<option value="{{ $tour->id }}" @selected(old('tour_id') == $tour->id)>{{ $tour->title }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="q_text">Your question</label>
                <textarea id="q_text" name="question" rows="4" required class="field">{{ old('question') }}</textarea>
                @error('question')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="btn btn-primary">Send question</button>
        </form>
    </div>
</section>