{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 01: PROMO BANNER — slim dismissible bar above nav        --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section
    x-data="{ visible: true }"
    x-show="visible"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="bg-neutral-900 text-white relative"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
        <div class="flex items-center justify-center gap-3 text-center">
            <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse-soft shrink-0"></span>
            {{--<p class="text-xs sm:text-sm font-medium tracking-wide">{{-- PLACEHOLDER: swap via admin Settings -- }}</p>--}}
            <p class="text-xs sm:text-sm font-medium tracking-wide">
                <span class="font-bold text-brand">Winter 2026 Series is live</span>
                <span class="opacity-60 mx-1">·</span>
                <span class="opacity-80">First 100 solvers ship free</span>
            </p>
            <a href="{{ url('/challenges') }}" class="hidden sm:inline text-xs font-semibold underline underline-offset-2 hover:text-brand transition-colors">Join now →</a>
        </div>
    </div>
    <button @click="visible = false" aria-label="Dismiss" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/60 hover:text-white transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</section>
