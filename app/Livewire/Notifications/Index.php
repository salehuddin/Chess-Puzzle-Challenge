<?php

namespace App\Livewire\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    public function render()
    {
        $notifications = auth()->user()
            ->notifications()
            ->whereIn('type', $this->playerNotificationClasses())
            ->latest()
            ->paginate(20);

        return view('livewire.notifications.index', compact('notifications'));
    }

    public function markRead(string $id): void
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification instanceof DatabaseNotification && in_array($notification->type, $this->playerNotificationClasses(), true)) {
            $notification->markAsRead();
        }
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->whereIn('type', $this->playerNotificationClasses())->update(['read_at' => now()]);
    }

    /** @return array<int, class-string> */
    private function playerNotificationClasses(): array
    {
        return array_map(
            fn (string $type): string => 'App\\Notifications\\'.str_replace(' ', '', ucwords(str_replace('_', ' ', $type))).'Notification',
            array_keys(User::playerNotificationTypes()),
        );
    }
}
