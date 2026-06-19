List <x-marketing-layout>
    <x-slot name="title">Mockup: Queen's Gambit Sprint — Chess Puzzle Challenge</x-slot>
    <x-slot name="description">Dummy single challenge landing page mockup for Chess Puzzle Challenge, inspired by high-converting virtual challenge layouts.</x-slot>

    <section class="relative overflow-hidden bg-stone-950 text-white">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(217,119,6,0.25),_transparent_45%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_rgba(22,163,74,0.25),_transparent_40%)]"></div>
            <div class="absolute inset-0 opacity-20 bg-chess-pattern"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-400/20 text-amber-200 px-4 py-1.5 text-xs font-semibold tracking-wider uppercase mb-5">
                        New Series Mockup
                    </div>

                    <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-5">
                        Queen's Gambit Sprint
                    </h1>

                    <p class="text-stone-200 text-lg leading-relaxed max-w-xl mb-7">
                        Take on a 30-day puzzle journey inspired by classic Queen's Gambit structures. Solve tactical strikes,
                        positional squeezes, and endgame conversions as you progress through five themed stages.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 mb-8">
                        <span class="badge badge-warning gap-1 py-3">120 puzzles</span>
                        <span class="badge badge-outline border-white/30 text-white gap-1 py-3">10 milestone postcards</span>
                        <span class="badge badge-outline border-white/30 text-white gap-1 py-3">Interactive board replay</span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="#mockup-pricing" class="btn btn-warning text-stone-900 btn-lg">Start This Challenge</a>
                        <a href="#mockup-features" class="btn btn-outline border-white/40 text-white hover:bg-white hover:text-stone-900 btn-lg">Explore Features</a>
                    </div>
                </div>

                <div class="relative">
                    <div class="rounded-3xl border border-white/20 bg-white/10 backdrop-blur p-6 shadow-2xl">
                        <p class="text-xs uppercase tracking-widest text-stone-300 mb-3">Featured Reward</p>
                        <div class="rounded-2xl bg-gradient-to-br from-amber-200 to-amber-500 p-6 text-stone-900">
                            <p class="text-sm font-semibold uppercase tracking-wider">Finisher Medal</p>
                            <p class="font-display text-3xl font-black mt-2">The Gambit Crown</p>
                            <p class="text-sm mt-2">Antique gold finish, engraved puzzle motif, numbered edition.</p>
                        </div>

                        <div class="grid grid-cols-3 gap-3 mt-4 text-center text-xs">
                            <div class="rounded-xl bg-white/10 p-3">
                                <p class="text-2xl font-black text-white">5</p>
                                <p class="text-stone-300">Stages</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-3">
                                <p class="text-2xl font-black text-white">30</p>
                                <p class="text-stone-300">Days</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-3">
                                <p class="text-2xl font-black text-white">1</p>
                                <p class="text-stone-300">Legendary medal</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-primary text-white py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                @foreach([
                    ['4.8/5', 'Player Rating'],
                    ['92%', 'Completion Rate'],
                    ['18m', 'Avg Solve Session'],
                    ['1,400+', 'Early Access Signups'],
                ] as [$value, $label])
                    <div>
                        <p class="font-display text-3xl md:text-4xl font-black mb-1">{{ $value }}</p>
                        <p class="text-xs md:text-sm text-white/80">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="mockup-features" class="py-20 bg-base-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <p class="text-primary text-sm font-semibold uppercase tracking-widest mb-3">Challenge Experience</p>
                <h2 class="font-display text-4xl font-black text-stone-900">What You Unlock Along The Journey</h2>
            </div>

            <div class="space-y-4">
                <details class="group rounded-2xl border border-stone-200 bg-white shadow-warm p-6" open>
                    <summary class="list-none cursor-pointer flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-display text-2xl font-bold text-stone-900">Virtually Climb The Tactics Ladder</h3>
                            <p class="text-stone-500 mt-2">Advance through five story chapters, each with escalating puzzle themes.</p>
                        </div>
                        <span class="text-primary text-2xl leading-none group-open:rotate-45 transition-transform">+</span>
                    </summary>
                    <div class="mt-4 text-stone-600 leading-relaxed">
                        Watch your progress move from Opening Traps to Endgame Accuracy on an interactive timeline board.
                        Every solved puzzle fills your chapter meter and unlocks chapter commentary cards.
                    </div>
                </details>

                <details class="group rounded-2xl border border-stone-200 bg-white shadow-warm p-6">
                    <summary class="list-none cursor-pointer flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-display text-2xl font-bold text-stone-900">Discover Signature Positions</h3>
                            <p class="text-stone-500 mt-2">Annotated mini-lessons from famous Queen's Gambit structures.</p>
                        </div>
                        <span class="text-primary text-2xl leading-none group-open:rotate-45 transition-transform">+</span>
                    </summary>
                    <div class="mt-4 text-stone-600 leading-relaxed">
                        Key puzzle checkpoints include classic hanging-pawn middlegames, minority attack themes,
                        and practical defensive resources from real tournament games.
                    </div>
                </details>

                <details class="group rounded-2xl border border-stone-200 bg-white shadow-warm p-6">
                    <summary class="list-none cursor-pointer flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-display text-2xl font-bold text-stone-900">Collect Digital Postcards</h3>
                            <p class="text-stone-500 mt-2">10 collectible cards with opening lore and tactical patterns.</p>
                        </div>
                        <span class="text-primary text-2xl leading-none group-open:rotate-45 transition-transform">+</span>
                    </summary>
                    <div class="mt-4 text-stone-600 leading-relaxed">
                        Each postcard contains a featured position, a strategic clue, and a challenge quote.
                        Complete all ten to unlock the hidden "Grandmaster Notes" card.
                    </div>
                </details>

                <details class="group rounded-2xl border border-stone-200 bg-white shadow-warm p-6">
                    <summary class="list-none cursor-pointer flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-display text-2xl font-bold text-stone-900">Train Solo Or In Squad Mode</h3>
                            <p class="text-stone-500 mt-2">Team progress mode for clubs, friends, and school groups.</p>
                        </div>
                        <span class="text-primary text-2xl leading-none group-open:rotate-45 transition-transform">+</span>
                    </summary>
                    <div class="mt-4 text-stone-600 leading-relaxed">
                        Enable squad mode to combine solved puzzle counts toward shared milestone rewards.
                        Track who contributed each tactical breakthrough in the squad feed.
                    </div>
                </details>
            </div>
        </div>
    </section>

    <section class="py-20 bg-base-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            @foreach([
                ['Track Every Move', 'Solve sessions sync instantly across desktop and mobile. Resume any puzzle from your last analyzed variation with no progress loss.', '♟'],
                ['Grow Through Real Feedback', 'Each solved puzzle can reveal layered hints and model continuation lines so players understand why a move works, not just what to play.', '🧠'],
                ['Play For A Real-World Impact', 'For every 20% completed in this mock challenge, we donate to youth chess education and board access programs.', '🌍'],
            ] as [$title, $description, $icon])
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-center rounded-3xl bg-white p-8 border border-stone-200 shadow-warm">
                    <div class="lg:col-span-1 flex justify-center lg:justify-start">
                        <div class="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center text-4xl">{{ $icon }}</div>
                    </div>
                    <div class="lg:col-span-4 text-center lg:text-left">
                        <h3 class="font-display text-3xl font-black text-stone-900 mb-2">{{ $title }}</h3>
                        <p class="text-stone-600 leading-relaxed max-w-3xl">{{ $description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="py-20 bg-base-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="font-display text-4xl font-black text-stone-900">Plus All This</h2>
                <p class="text-stone-500 mt-3">Everything included in this single challenge mockup package.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['Access Anywhere', 'Log in from browser, tablet, or phone with synchronized puzzle history.', '📱'],
                    ['Multiple Difficulty Tracks', 'Choose adaptive mode or fixed-rating mode based on your level.', '🎚'],
                    ['Flexible Timeframe', 'Set your own completion target: 2 weeks, 30 days, or no deadline.', '⏳'],
                    ['Solo Or Team', 'Create a private squad and solve toward a shared completion banner.', '👥'],
                ] as [$title, $desc, $icon])
                    <article class="rounded-2xl border border-stone-200 p-6 bg-white shadow-warm">
                        <div class="text-3xl mb-3">{{ $icon }}</div>
                        <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ $title }}</h3>
                        <p class="text-sm text-stone-600 leading-relaxed">{{ $desc }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-b from-stone-100 to-amber-50/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="font-display text-4xl font-black text-stone-900">Collectible Reward Set</h2>
                <p class="text-stone-600 mt-3">Dummy merch block adapted for chess challenge rewards.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach([
                    ['Finisher Medal', 'Antique gold with rotating center puzzle glyph.', 'Limited Edition'],
                    ['Puzzle Master Tee', 'Black cotton tee with opening-tree print on back.', '4 Colors | XS-4XL'],
                    ['Desk Display Card', 'Acrylic mini stand featuring your completion badge.', 'Included in Premium'],
                ] as [$title, $description, $meta])
                    <article class="rounded-2xl bg-white border border-stone-200 p-6 shadow-warm">
                        <div class="h-36 rounded-xl bg-stone-100 mb-4 flex items-center justify-center text-stone-400 text-sm">
                            Mockup image placeholder
                        </div>
                        <h3 class="font-display text-2xl font-bold text-stone-900 mb-2">{{ $title }}</h3>
                        <p class="text-stone-600 text-sm mb-3">{{ $description }}</p>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $meta }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="mockup-pricing" class="py-20 bg-base-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="font-display text-4xl font-black text-stone-900">Join The Queen's Gambit Sprint</h2>
                <p class="text-stone-500 mt-3">Dummy pricing cards for design exploration.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <article class="rounded-3xl border border-stone-200 bg-white p-8 shadow-warm">
                    <h3 class="font-display text-3xl font-black text-stone-900 mb-2">Entry + Medal</h3>
                    <p class="text-4xl font-black text-primary mb-5">MYR 139.00</p>
                    <ul class="space-y-2 text-stone-600 text-sm mb-7">
                        <li>120 curated puzzles with chapter progression</li>
                        <li>10 collectible virtual postcards</li>
                        <li>Digital completion certificate</li>
                        <li>Physical finisher medal shipped on completion</li>
                        <li>Access to challenge community feed</li>
                    </ul>
                    <a href="#" class="btn btn-primary w-full">Choose Entry + Medal</a>
                </article>

                <article class="rounded-3xl border-2 border-amber-400 bg-gradient-to-b from-amber-50 to-white p-8 shadow-warm-lg relative">
                    <span class="absolute -top-3 right-6 bg-amber-400 text-stone-900 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Most Popular</span>
                    <h3 class="font-display text-3xl font-black text-stone-900 mb-2">Entry + Medal + Premium Pack</h3>
                    <p class="text-4xl font-black text-primary mb-5">MYR 249.00</p>
                    <ul class="space-y-2 text-stone-600 text-sm mb-7">
                        <li>Everything in Entry + Medal</li>
                        <li>Puzzle Master Tee (any size, any color)</li>
                        <li>Acrylic desk display completion card</li>
                        <li>Priority shipping for all rewards</li>
                        <li>Exclusive endgame mini-pack (20 bonus puzzles)</li>
                    </ul>
                    <a href="#" class="btn btn-warning text-stone-900 w-full">Choose Premium Pack</a>
                </article>
            </div>
        </div>
    </section>

    <section class="py-14 bg-stone-900 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-white/20 bg-white/5 p-8 lg:p-10 grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                <div class="lg:col-span-2">
                    <h3 class="font-display text-3xl font-black mb-3">30-Day Refund Policy</h3>
                    <p class="text-stone-200 leading-relaxed">
                        If you have not started this challenge and need to cancel, we offer a simple no-questions-asked refund.
                        This is mockup content for checkout confidence and trust placement.
                    </p>
                </div>
                <div class="text-center lg:text-right">
                    <p class="text-5xl font-black text-amber-300">1M+</p>
                    <p class="text-stone-300 text-sm uppercase tracking-widest">Virtual challengers served</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-base-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4 mb-8">
                <div>
                    <p class="text-primary text-sm font-semibold uppercase tracking-widest mb-2">Recommended Next</p>
                    <h2 class="font-display text-4xl font-black text-stone-900">Other Puzzle Challenges You May Love</h2>
                </div>
                <a href="#" class="btn btn-outline btn-primary">Browse All Challenges</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['Sicilian Counterstrike', '90 puzzles', '7 postcards'],
                    ['Endgame Fortress', '110 puzzles', '8 postcards'],
                    ['Tactical Storm: Kingside', '130 puzzles', '11 postcards'],
                    ['Rook Lift Legends', '80 puzzles', '6 postcards'],
                ] as [$name, $distance, $postcards])
                    <article class="rounded-2xl bg-white border border-stone-200 overflow-hidden shadow-warm hover:shadow-warm-lg transition-shadow">
                        <div class="h-28 bg-chess-pattern-green"></div>
                        <div class="p-5">
                            <h3 class="font-display text-2xl font-bold text-stone-900 mb-2">{{ $name }}</h3>
                            <p class="text-sm text-stone-600 mb-4">{{ $distance }} • {{ $postcards }}</p>
                            <a href="#" class="btn btn-sm btn-primary w-full">View Challenge</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</x-marketing-layout>