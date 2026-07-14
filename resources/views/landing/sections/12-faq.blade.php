{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION 12: FAQ — single-column accordion with chartreuse chevron  --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<section id="faq" class="bg-white py-20 lg:py-28 relative overflow-hidden">

    <div class="absolute inset-0 bg-radial-brand-tl pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="reveal text-center mb-12">
            <span class="inline-block text-neutral-500 font-bold text-xs uppercase tracking-[0.2em] mb-3">Frequently Asked Questions</span>
            <h2 class="font-display text-4xl lg:text-5xl font-black text-neutral-900">You've got questions,<br>we've got answers.</h2>
        </div>

        @php
            $faqs = [
                ['q' => 'How do I solve a puzzle?',
                 'a' => "Just click-to-move on the interactive board. Pieces follow standard chess rules. Try a move — if it's wrong, you can undo freely without penalty. Stuck? Tap Hint and the piece to move will be highlighted."],
                ['q' => 'How long does a series take?',
                 'a' => "Each series is 100 puzzles. At your own pace, that's anywhere from a few evenings to a few weeks. No daily streaks, no penalties for taking a break."],
                ['q' => 'When can I start?',
                 'a' => "Any time after purchase. The moment your order is paid, your series unlocks and you can begin solving immediately — no waiting."],
                ['q' => 'Can I join multiple series at once?',
                 'a' => "Yes. You can have as many active series as you like. Bundles are the best-value way to do this — multiple series in one purchase, with each medal shipping individually as you complete each series."],
                ['q' => 'What puzzle themes are included?',
                 'a' => "Each series is themed around a single tactical pattern — forks, pins, mating attacks, endgames, opening traps, back-rank mates, knight tactics, or quiet moves / zugzwang. The theme is shown on every series page."],
                ['q' => 'Can I solve puzzles out of order?',
                 'a' => "Depends on the series. Some are sequential (each puzzle unlocks the next), others let you solve in any order. The rule is shown on the series page."],
                ['q' => 'How does the visual hint work?',
                 'a' => "Tap the hint button and the piece you need to move gets highlighted on the board. We count how many hints you use (saved to your browser) — but there's no penalty. The puzzle already knows the answer; we use the solution to highlight the piece, not a live engine."],
                ['q' => 'Do I need a Lichess account?',
                 'a' => "No. Our puzzles are sourced from the open Lichess puzzle database, but you don't need a Lichess account to play — just an account on our platform."],
                ['q' => 'Where do you ship?',
                 'a' => "Worldwide — over 80 countries. A small set of countries has shipping restrictions due to international regulations. You'll see the list at checkout before payment."],
                ['q' => 'When will my medal arrive?',
                 'a' => "After you complete the series, your medal is custom-made and shipped. Production + shipping typically takes a few weeks; you'll get a courier tracking number by email when it ships."],
                ['q' => 'How does PPP pricing work?',
                 'a' => "We use Purchasing Power Parity pricing. Malaysian IPs see prices in MYR, all other countries see USD. This keeps pricing fair for your region — chess should be affordable everywhere."],
                ['q' => 'Is my progress saved?',
                 'a' => "Yes — your progress is saved in your browser's local storage. Close the tab, refresh, or take a week off and you'll pick up exactly where you left off, puzzle by puzzle."],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-data="{ open: 0 }">
            @foreach($faqs as $i => $faq)
                <div class="reveal border border-neutral-200 rounded-2xl overflow-hidden hover:border-brand/40 transition-colors"
                     style="--reveal-delay: {{ $i * 50 }}ms;">
                    <button
                        @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                        :class="open === {{ $i }} ? 'bg-neutral-50' : 'bg-white'"
                        class="w-full text-left px-6 py-4 flex items-center justify-between gap-4 transition-colors"
                        :aria-expanded="open === {{ $i }}"
                    >
                        <span class="font-display font-bold text-neutral-900 text-base">{{ $faq['q'] }}</span>
                        <svg class="w-5 h-5 shrink-0 transition-transform duration-300 {{ $i === 0 ? '' : '' }}"
                             :class="open === {{ $i }} ? 'rotate-180 text-brand' : 'text-neutral-400'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div
                        x-show="open === {{ $i }}"
                        x-collapse
                        x-cloak
                    >
                        <p class="px-6 pb-4 text-neutral-600 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Money-back guarantee badge (folded into FAQ per plan) --}}
        <div class="reveal mt-12 flex items-center justify-center gap-3 text-sm text-neutral-500">
            <span class="text-brand text-lg">🛡</span>
            <span>
                <strong class="font-bold text-neutral-900">30-Day Money-Back Guarantee</strong> — Got busy and haven't started? Contact us within 30 days for a no-questions-asked refund.
            </span>
        </div>
    </div>
</section>
