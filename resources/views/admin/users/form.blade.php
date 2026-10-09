@php
    $isEdit = $user->exists;
    $in = 'mt-1 block w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500';
    $lbl = 'block text-sm font-medium text-stone-700';
    $currentRole = old('role', $isEdit ? $user->roles->first()?->name : \App\Enums\RoleName::Support->value);
@endphp

<x-admin.layout :title="$isEdit ? 'Edit staff user' : 'New staff user'">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-stone-900">{{ $isEdit ? 'Edit '.$user->name : 'New staff user' }}</h1>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-stone-600 hover:text-amber-700">&larr; All staff</a>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}"
          class="max-w-xl space-y-5 rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label class="{{ $lbl }}" for="name">Name *</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="{{ $in }}">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lbl }}" for="email">Email *</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="{{ $in }}">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lbl }}" for="role">Role *</label>
            <select id="role" name="role" class="{{ $in }}">
                @foreach (\App\Enums\RoleName::cases() as $role)
                    <option value="{{ $role->value }}" @selected($currentRole === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-stone-500">Super Admin: everything. Admin: all but staff users. Editor: content. Support: bookings, customers and moderation.</p>
        </div>
        <div>
            <label class="{{ $lbl }}" for="password">{{ $isEdit ? 'New password (leave blank to keep)' : 'Password *' }}</label>
            <input id="password" name="password" type="password" autocomplete="new-password" class="{{ $in }}">
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lbl }}" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $in }}">
        </div>

        <div class="flex justify-end gap-3 border-t border-stone-100 pt-4">
            <a href="{{ route('admin.users.index') }}" class="rounded-md border border-stone-300 bg-white px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">Cancel</a>
            <button class="rounded-md bg-amber-600 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-700">{{ $isEdit ? 'Save' : 'Create' }}</button>
        </div>
    </form>
</x-admin.layout>