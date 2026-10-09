<div x-data="{ open: false, action: '', name: 'this item' }"
     x-on:confirm-delete.window="open = true; action = $event.detail.action; name = $event.detail.name ?? 'this item'"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     role="dialog" aria-modal="true">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.outside="open = false">
        <h3 class="text-lg font-semibold text-stone-900">Delete <span x-text="name"></span>?</h3>
        <p class="mt-2 text-sm text-stone-600">Do you really want to delete it? This cannot be undone.</p>

        <form method="POST" :action="action" class="mt-6 flex justify-end gap-3">
            @csrf
            @method('DELETE')
            <button type="button" @click="open = false"
                    class="rounded-md border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">No, keep it</button>
            <button type="submit"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Yes, delete it</button>
        </form>
    </div>
</div>