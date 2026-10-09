@props(['items'])

<div x-data="{ open: null }" class="divide-y divide-stone-200 rounded-xl border border-stone-200 bg-white">
    @foreach ($items as $i => $item)
        <div>
            <button type="button" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold text-stone-900"
                    @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}">
                <span>{{ $item->question }}</span>
                <span class="text-xl leading-none text-amber-600" x-text="open === {{ $i }} ? '-' : '+'"></span>
            </button>
            <div x-show="open === {{ $i }}" x-cloak x-transition.opacity class="px-5 pb-4 text-sm leading-relaxed text-stone-700">
                {!! nl2br(e($item->answer)) !!}
            </div>
        </div>
    @endforeach
</div>