<x-admin.layout title="Staff users">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">Staff users</h1>
        <a href="{{ route('admin.users.create') }}" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">+ New staff user</a>
    </div>

    <form method="GET" class="mb-4 flex gap-3 rounded-lg bg-white p-4 shadow-sm">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or email" class="w-64 rounded-md border-stone-300 text-sm">
        <button class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Search</button>
    </form>

    <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 text-xs uppercase text-stone-500">
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}@if ($user->is(auth()->user())) <span class="text-xs text-stone-400">(you)</span>@endif</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->roles->pluck('name')->map(fn ($r) => \Illuminate\Support\Str::headline($r))->join(', ') }}</td>
                        <td class="space-x-3 px-4 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-amber-700 hover:underline">Edit</a>
                            @unless ($user->is(auth()->user()))
                                <button type="button" x-data class="text-red-600 hover:underline"
                                        @click="$dispatch('confirm-delete', { action: '{{ route('admin.users.destroy', $user) }}', name: @js($user->name) })">Delete</button>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-admin.layout>