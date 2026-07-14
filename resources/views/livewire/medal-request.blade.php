<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-neutral-500 hover:text-neutral-700 transition">
            <span>&larr;</span>
            <span>Back to Dashboard</span>
        </a>
    </div>

    @if($alreadyRequested)
        <div class="bg-white rounded-3xl shadow-xl border border-neutral-100 overflow-hidden">
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 px-8 py-12 text-center border-b border-orange-100">
                <div class="w-24 h-24 mx-auto mb-4 text-orange-500">
                    <svg class="w-full h-full" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <h1 class="font-display text-3xl font-black text-neutral-900 mb-2">Medal Already Requested!</h1>
                <p class="text-neutral-600">Your physical medal for <strong>{{ $challenge->name }}</strong> is being prepared for shipment.</p>
            </div>
            <div class="p-8 text-center">
                <a href="{{ route('orders.track', $enrollment) }}" class="btn btn-primary gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    Track My Medal
                </a>
            </div>
        </div>
    @else
        <form wire:submit="requestMedal">
            <div class="bg-white rounded-3xl shadow-xl border border-neutral-100 overflow-hidden mb-6">
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 px-8 py-12 text-center border-b border-orange-100">
                    <div class="w-24 h-24 mx-auto mb-4">
                        <svg class="w-full h-full text-orange-500 drop-shadow-md" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                    </div>
                    <h1 class="font-display text-3xl font-black text-neutral-900 mb-2">Claim Your Physical Medal</h1>
                    <p class="text-neutral-600 max-w-md mx-auto">You conquered <strong>{{ $challenge->name }}</strong>! Confirm your shipping address below and we'll mail your medal.</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-neutral-100 overflow-hidden mb-6">
                <div class="px-8 py-6 border-b border-neutral-100 bg-neutral-50">
                    <h2 class="font-display text-xl font-bold text-neutral-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Shipping Address
                    </h2>
                    <p class="text-sm text-neutral-500 mt-1">This address will be used to ship your physical medal. Edit any field below if needed.</p>
                </div>

                <div class="p-8 space-y-5">
                    <div>
                        <label for="addressLine1" class="block text-sm font-semibold text-neutral-700 mb-1.5">Address Line 1 <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="addressLine1"
                            wire:model="addressLine1"
                            placeholder="House number and street"
                            class="input input-bordered w-full @error('addressLine1') input-error @enderror"
                        />
                        @error('addressLine1') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="addressLine2" class="block text-sm font-semibold text-neutral-700 mb-1.5">Address Line 2</label>
                        <input
                            type="text"
                            id="addressLine2"
                            wire:model="addressLine2"
                            placeholder="Apartment, suite, unit (optional)"
                            class="input input-bordered w-full @error('addressLine2') input-error @enderror"
                        />
                        @error('addressLine2') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="city" class="block text-sm font-semibold text-neutral-700 mb-1.5">City <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="city"
                                wire:model="city"
                                class="input input-bordered w-full @error('city') input-error @enderror"
                            />
                            @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-semibold text-neutral-700 mb-1.5">State / Province <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="state"
                                wire:model="state"
                                class="input input-bordered w-full @error('state') input-error @enderror"
                            />
                            @error('state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="postcode" class="block text-sm font-semibold text-neutral-700 mb-1.5">Postal Code <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="postcode"
                                wire:model="postcode"
                                class="input input-bordered w-full @error('postcode') input-error @enderror"
                            />
                            @error('postcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-semibold text-neutral-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="country"
                                wire:model="country"
                                class="input input-bordered w-full @error('country') input-error @enderror"
                            />
                            @error('country') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <label class="flex items-center gap-3 pt-2 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="saveToProfile"
                            class="checkbox checkbox-primary checkbox-sm"
                        />
                        <span class="text-sm text-neutral-600">Also update my default profile address with these details</span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <button type="button" wire:click="requestLater" class="btn btn-ghost border border-neutral-200">
                    I'll request it later
                </button>
                <button type="submit" class="btn btn-primary btn-lg gap-2" wire:loading.attr="disabled" wire:target="requestMedal">
                    <svg wire:loading.remove wire:target="requestMedal" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    <svg wire:loading wire:target="requestMedal" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Request My Medal
                </button>
            </div>
        </form>
    @endif
</div>
