<section>
    <header>
        <h2 class="text-lg font-medium text-neutral-900">
            {{ __('Default Shipping Address') }}
        </h2>

        <p class="mt-1 text-sm text-neutral-600">
            {{ __("Ensure your default shipping address is accurate so your medals/stickers reach you.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="address_line1" :value="__('Address Line 1')" />
            <x-text-input id="address_line1" name="address_line1" type="text" class="mt-1 block w-full" :value="old('address_line1', $user->address_line1)" autocomplete="address-line1" />
            <x-input-error class="mt-2" :messages="$errors->get('address_line1')" />
        </div>

        <div>
            <x-input-label for="address_line2" :value="__('Address Line 2 (Optional)')" />
            <x-text-input id="address_line2" name="address_line2" type="text" class="mt-1 block w-full" :value="old('address_line2', $user->address_line2)" autocomplete="address-line2" />
            <x-input-error class="mt-2" :messages="$errors->get('address_line2')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            <div>
                <x-input-label for="state" :value="__('State / Province')" />
                <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->state)" autocomplete="address-level1" />
                <x-input-error class="mt-2" :messages="$errors->get('state')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="postcode" :value="__('Postal Code')" />
                <x-text-input id="postcode" name="postcode" type="text" class="mt-1 block w-full" :value="old('postcode', $user->postcode)" autocomplete="postal-code" />
                <x-input-error class="mt-2" :messages="$errors->get('postcode')" />
            </div>

            <div>
                <x-input-label for="country" :value="__('Country')" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $user->country)" autocomplete="country" />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Address') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-neutral-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
