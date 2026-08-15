<div>
    <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">Notification preferences</h2>
        <p class="mt-1 text-sm text-gray-600">Choose which player updates appear in your account and arrive by email.</p>
    </div>

    @if(session('notification-preferences-updated'))
        <p class="mb-4 text-sm font-medium text-green-600">{{ session('notification-preferences-updated') }}</p>
    @endif

    <div class="overflow-hidden rounded-xl border border-neutral-200">
        <div class="grid grid-cols-[1fr_auto_auto] gap-4 bg-neutral-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">
            <span>Update</span><span>In account</span><span>Email</span>
        </div>
        @foreach($types as $type => $label)
            <div class="grid grid-cols-[1fr_auto_auto] items-center gap-4 border-t border-neutral-100 px-4 py-3">
                <span class="text-sm text-neutral-700">{{ $label }}</span>
                <input type="checkbox" wire:model="preferences.{{ $type }}.database" class="checkbox checkbox-primary checkbox-sm" aria-label="{{ $label }} in account">
                <input type="checkbox" wire:model="preferences.{{ $type }}.mail" class="checkbox checkbox-primary checkbox-sm" aria-label="{{ $label }} by email">
            </div>
        @endforeach
    </div>

    <button type="button" wire:click="save" class="btn btn-primary btn-sm mt-5">Save preferences</button>
</div>
