@if (session('status') || $errors->has('booking'))
    <div {{ $attributes }}>
        @if (session('status'))
            <div class="mb-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if ($errors->has('booking'))
            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('booking') }}</div>
        @endif
    </div>
@endif