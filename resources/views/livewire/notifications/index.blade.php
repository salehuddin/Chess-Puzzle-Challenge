<div>
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-primary">Your account</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-neutral-900">Notifications</h1>
            <p class="mt-2 text-neutral-500">Updates about your challenges, medals, and reviews.</p>
        </div>
        <button type="button" wire:click="markAllRead" class="btn btn-outline btn-sm">Mark all as read</button>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div class="rounded-2xl border border-neutral-100 bg-white p-5 shadow-warm {{ $notification->read() ? '' : 'border-primary/30 bg-orange-50/40' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-neutral-800">{{ data_get($notification->data, 'title', 'Notification') }}</h2>
                        <p class="mt-1 text-sm leading-relaxed text-neutral-600">{{ data_get($notification->data, 'body') }}</p>
                        <p class="mt-2 text-xs text-neutral-400">{{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                    @if(! $notification->read())
                        <button type="button" wire:click="markRead('{{ $notification->id }}')" class="shrink-0 text-xs font-semibold text-primary hover:underline">Mark read</button>
                    @endif
                </div>
                @if(data_get($notification->data, 'url'))
                    <a href="{{ data_get($notification->data, 'url') }}" wire:click="markRead('{{ $notification->id }}')" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">Open update →</a>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-neutral-200 bg-white p-12 text-center text-neutral-500">You are all caught up.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $notifications->links() }}</div>
</div>
