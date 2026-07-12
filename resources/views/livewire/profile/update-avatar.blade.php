<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Avatar') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Upload a square image (JPG, PNG, or WebP, max 1 MB). Used as your profile picture in the navigation and public profile.') }}
        </p>
    </header>

    <div class="mt-6 flex items-start gap-6">
        {{-- Current avatar preview --}}
        <div class="shrink-0">
            @if($currentAvatar)
                <img src="{{ Storage::url($currentAvatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover border-2 border-stone-200" />
            @else
                <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-2xl border-2 border-stone-200">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            @endif
        </div>

        {{-- Upload controls --}}
        <div class="flex-1 space-y-4">
            <div>
                <input
                    type="file"
                    wire:model="photo"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-sm text-stone-500
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-lg file:border-0
                           file:text-sm file:font-semibold
                           file:bg-primary/10 file:text-primary
                           hover:file:bg-primary/20
                           file:cursor-pointer"
                />
                @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Preview of pending upload --}}
            @if($photo)
                <div class="flex items-center gap-4">
                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-16 h-16 rounded-full object-cover border-2 border-primary/30" />
                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="btn btn-primary btn-sm"
                    >
                        <span wire:loading.remove wire:target="save">Save Avatar</span>
                        <span wire:loading wire:target="save">Uploading...</span>
                    </button>
                    <button
                        wire:click="$set('photo', null)"
                        class="btn btn-ghost btn-sm"
                    >
                        Cancel
                    </button>
                </div>
            @endif

            @if($currentAvatar && !$photo)
                <button
                    wire:click="remove"
                    wire:confirm="Remove your avatar?"
                    class="text-sm text-red-600 hover:text-red-800 underline"
                >
                    Remove avatar
                </button>
            @endif
        </div>
    </div>
</section>
