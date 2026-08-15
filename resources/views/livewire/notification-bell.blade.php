<div wire:poll.30s="refreshNotifications" class="relative">
    <div class="dropdown dropdown-end">
        <button tabindex="0" type="button" class="btn btn-ghost btn-circle" aria-label="Notifications">
            <svg class="h-5 w-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 1 0-12 0v.75a8.967 8.967 0 0 1-2.31 6.022c1.733.64 3.55 1.088 5.454 1.31m5.713 0a24.255 24.255 0 0 1-5.713 0m5.713 0a3 3 0 1 1-5.713 0" />
            </svg>
            @if($unreadCount > 0)
                <span class="badge badge-error badge-xs absolute right-0 top-0 text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </button>
        <div tabindex="0" class="dropdown-content z-50 mt-3 w-80 rounded-2xl border border-orange-100 bg-white p-3 shadow-warm-lg">
            <div class="flex items-center justify-between border-b border-neutral-100 px-2 pb-2">
                <p class="font-semibold text-neutral-800">Notifications</p>
                @if($unreadCount > 0)
                    <button type="button" wire:click="markAllRead" class="text-xs text-primary hover:underline">Mark all read</button>
                @endif
            </div>
            <div class="max-h-80 overflow-y-auto py-1">
                @forelse($items as $item)
                    <button type="button" wire:click="openNotification('{{ $item['id'] }}')" class="block w-full rounded-xl px-2 py-3 text-left hover:bg-orange-50 {{ $item['read'] ? '' : 'bg-orange-50/60' }}">
                        <p class="text-sm font-semibold text-neutral-800">{{ $item['title'] }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-neutral-500">{{ $item['body'] }}</p>
                        <p class="mt-1 text-[11px] text-neutral-400">{{ $item['created_at'] }}</p>
                    </button>
                @empty
                    <p class="px-2 py-6 text-center text-sm text-neutral-500">You are all caught up.</p>
                @endforelse
            </div>
            <a href="{{ route('notifications.index') }}" class="mt-1 block border-t border-neutral-100 px-2 pt-2 text-center text-xs font-semibold text-primary hover:underline">View all notifications</a>
        </div>
    </div>
</div>
