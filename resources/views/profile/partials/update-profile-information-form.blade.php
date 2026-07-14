<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <div class="mt-1 flex items-center gap-1">
                <span class="text-sm text-neutral-400">chesspuzzlechallenge.com/u/</span>
            </div>
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" placeholder="your-username" autocomplete="username" />
            <p class="mt-1 text-xs text-neutral-400">Lowercase letters, numbers, and hyphens only (3–30 characters). Required for a public profile.</p>
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea
                id="bio"
                name="bio"
                rows="3"
                maxlength="500"
                class="mt-1 block w-full rounded-lg border-neutral-300 shadow-sm focus:border-primary focus:ring-primary text-sm"
                placeholder="Tell others about yourself..."
            >{{ old('bio', $user->bio) }}</textarea>
            <p class="mt-1 text-xs text-neutral-400">{{ strlen($user->bio ?? '') }}/500 characters</p>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="hidden"
                    name="profile_is_public"
                    value="0"
                />
                <input
                    type="checkbox"
                    name="profile_is_public"
                    value="1"
                    @checked(old('profile_is_public', $user->profile_is_public))
                    class="checkbox checkbox-primary"
                    {{ $user->username ? '' : 'disabled title="Set a username first"' }}
                />
                <div>
                    <span class="text-sm font-medium text-neutral-800">Make profile public</span>
                    <p class="text-xs text-neutral-400">
                        @if($user->username)
                            Others can view your profile at <a href="{{ route('profile.show', $user->username) }}" target="_blank" class="underline text-primary">{{ url('/u/' . $user->username) }}</a>
                        @else
                            Set a username above to enable your public profile.
                        @endif
                    </p>
                </div>
            </label>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if($user->isPubliclyViewable())
                <a href="{{ route('profile.show', $user->username) }}" target="_blank" class="btn btn-ghost btn-sm gap-1">
                    View Public Profile
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
