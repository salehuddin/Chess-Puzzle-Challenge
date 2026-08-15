<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Livewire\Component;

class NotificationPreferences extends Component
{
    /** @var array<string, array<string, bool>> */
    public array $preferences = [];

    public function mount(): void
    {
        $this->preferences = auth()->user()->notificationPreferences();
    }

    public function save(): void
    {
        $this->validate([
            'preferences' => ['array'],
            'preferences.*.database' => ['boolean'],
            'preferences.*.mail' => ['boolean'],
        ]);

        $allowed = array_keys(User::playerNotificationTypes());
        $preferences = [];

        foreach ($allowed as $type) {
            $preferences[$type] = [
                'database' => (bool) data_get($this->preferences, "{$type}.database", true),
                'mail' => (bool) data_get($this->preferences, "{$type}.mail", true),
            ];
        }

        auth()->user()->forceFill(['notification_preferences' => $preferences])->save();

        session()->flash('notification-preferences-updated', 'Notification preferences updated.');
    }

    public function render()
    {
        return view('livewire.profile.notification-preferences', [
            'types' => User::playerNotificationTypes(),
        ]);
    }
}
