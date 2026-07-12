<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UpdateAvatar extends Component
{
    use WithFileUploads;

    #[Title('Avatar')]
    public ?TemporaryUploadedFile $photo = null;

    public ?string $currentAvatar = null;

    public function mount(): void
    {
        $this->currentAvatar = Auth::user()->avatar;
    }

    public function updatedPhoto(): void
    {
        $this->validate([
            'photo' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:ratio=1/1'],
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:ratio=1/1'],
        ]);

        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $ext = $this->photo->getClientOriginalExtension();
        $path = $this->photo->storeAs('avatars', "user-{$user->id}.{$ext}", 'public');

        $user->update(['avatar' => $path]);

        $this->currentAvatar = $path;
        $this->photo = null;

        $this->dispatch('avatar-updated', avatarUrl: Storage::url($path));
    }

    public function remove(): void
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        $this->currentAvatar = null;
        $this->photo = null;

        $this->dispatch('avatar-updated', avatarUrl: null);
    }

    public function render()
    {
        return view('livewire.profile.update-avatar');
    }
}
