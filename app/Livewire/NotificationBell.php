<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

class NotificationBell extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshNotifications();
    }

    public function refreshNotifications(): void
    {
        $notifications = auth()->user()
            ->notifications()
            ->whereIn('type', $this->playerNotificationClasses())
            ->latest()
            ->limit(8)
            ->get();

        $this->items = $notifications->map(fn (DatabaseNotification $notification): array => [
            'id' => $notification->id,
            'title' => data_get($notification->data, 'title', 'Notification'),
            'body' => data_get($notification->data, 'body', ''),
            'url' => data_get($notification->data, 'url', route('notifications.index')),
            'read' => $notification->read(),
            'created_at' => $notification->created_at?->diffForHumans(),
        ])->all();

        $this->unreadCount = auth()->user()
            ->unreadNotifications()
            ->whereIn('type', $this->playerNotificationClasses())
            ->count();
    }

    public function openNotification(string $id): void
    {
        $notification = auth()->user()->notifications()->find($id);

        if (! $notification || ! in_array($notification->type, $this->playerNotificationClasses(), true)) {
            return;
        }

        $notification->markAsRead();
        $this->redirect(data_get($notification->data, 'url', route('notifications.index')));
    }

    public function markAllRead(): void
    {
        auth()->user()
            ->unreadNotifications()
            ->whereIn('type', $this->playerNotificationClasses())
            ->update(['read_at' => now()]);

        $this->refreshNotifications();
    }

    public function render()
    {
        return view('livewire.notification-bell');
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
