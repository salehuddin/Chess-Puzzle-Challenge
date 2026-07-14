<x-slot name="title">{{ $challenge->name }} — Chess Puzzle Challenge</x-slot>

@php
    $levelData = match (true) {
        str_contains(strtolower($challenge->name), 'beginner')     => ['🌱', 'Beginner', 'badge-success'],
        str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'badge-warning'],
        str_contains(strtolower($challenge->name), 'advanced')     => ['🔥', 'Advanced', 'badge-error'],
        default                                                    => ['♟', 'Challenge', 'badge-primary'],
    };

    $rules         = is_array($challenge->rules) ? $challenge->rules : [];
    $puzzleTotal   = max((int) ($challenge->puzzles_count ?? $challenge->puzzle_count ?? 0), 0);
    $timeLimit     = (int) ($rules['time_limit_minutes'] ?? 0);
    $orderLabel    = ($rules['order'] ?? 'sequential') === 'sequential' ? 'Sequential order' : 'Free order';
    $description   = (string) ($challenge->description ?? '');
    $hasDescription = trim(strip_tags($description)) !== '';
    $termsHtml     = (string) ($challenge->terms_and_conditions ?? '');
    $hasTerms      = trim(strip_tags($termsHtml)) !== '';
    $faqItems      = is_array($challenge->faq) ? $challenge->faq : [];

    // Placeholder FAQ shown when the challenge has no FAQ authored yet.
    $placeholderFaqs = [
        ['question' => 'How do I start the challenge?', 'answer' => 'Once you enroll, your puzzles unlock immediately. Open the first puzzle from your dashboard and start solving.'],
        ['question' => 'How long do I have to finish?', 'answer' => 'There is no time limit. Take a week or take a year — your enrollment stays open until you complete every puzzle.'],
        ['question' => 'When will I receive my medal?', 'answer' => 'Your physical medal is dispatched within 3-5 business days of completing the final puzzle. You will receive a tracking link by email.'],
        ['question' => 'Can I replay puzzles I have already solved?', 'answer' => 'Yes. Once a puzzle is solved it stays accessible from your progress page. You can revisit it any time for practice.'],
        ['question' => 'What happens if I get stuck on a puzzle?', 'answer' => 'Each puzzle includes hint options. You can also revisit earlier puzzles or reach out in our community forum for guidance.'],
    ];

    $enrollUrl    = route('challenges.enroll', ['challenge' => $challenge], absolute: false);
    $registerUrl  = route('register', ['redirect_to' => $enrollUrl]);
    $loginUrl     = route('login', ['redirect_to' => $enrollUrl]);
    $challengesUrl = route('challenges.index');

    $checkoutUrl = ($userEnrollment['order_id'] ?? null)
        ? route('checkout.show', $userEnrollment['order_id'])
        : null;
    $playUrl = ($userEnrollment['id'] ?? null) ? route('play', $userEnrollment['id']) : null;
    $trackUrl = ($userEnrollment['id'] ?? null) ? route('orders.track', $userEnrollment['id']) : null;

    $isGuest = ! auth()->check();
    $isAdmin = auth()->user()?->isAdmin() ?? false;

    $heroMedia = array_values(array_filter([
        $posterImageUrl,
        $medalArtworkUrl,
        ...$imageGallery,
    ]));

    $status     = $userEnrollment['status'] ?? null;
    $stickyCtaLabel = match (true) {
        $status === 'active'    => 'Continue Playing',
        $status === 'pending'   => 'Complete Payment',
        $status === 'completed' => 'View Details',
        default                 => $isGuest ? 'Get Started' : ($isAdmin ? 'Enroll as Admin' : 'Enroll Now'),
    };
    $stickyCtaHref = match (true) {
        $status === 'active' && $playUrl     => $playUrl,
        $status === 'pending' && $checkoutUrl => $checkoutUrl,
        $status === 'completed' && $trackUrl  => $trackUrl,
        $isGuest                              => $registerUrl,
        default                              => $enrollUrl,
    };
    $stickySecondaryLabel = match (true) {
        $status === null && $isGuest         => 'Sign In',
        $status === null                      => 'Browse Challenges',
        $status === 'active'                  => 'Browse Challenges',
        $status === 'pending'                 => 'View Enrollment',
        $status === 'completed'               => 'Next Challenge',
        default                               => null,
    };
    $stickySecondaryHref = match (true) {
        $status === null && $isGuest         => $loginUrl,
        $status === null                      => $challengesUrl,
        $status === 'active'                  => $challengesUrl,
        $status === 'pending' && $trackUrl    => $trackUrl,
        $status === 'completed'               => $challengesUrl,
        default                               => null,
    };
    $stickyTitle = match (true) {
        $status === 'active'    => "You're enrolled.",
        $status === 'pending'   => 'Finish your enrollment',
        $status === 'completed' => 'Challenge completed!',
        default                 => $isGuest ? 'Ready to play?' : 'Ready to start?',
    };
    $stickySubtitle = match (true) {
        $status === 'active'    => 'Pick up where you left off.',
        $status === 'pending'   => 'Complete payment to unlock the puzzles.',
        $status === 'completed' => 'Track your medal shipment or browse more.',
        default                 => 'Enroll in this challenge to start solving.',
    };
@endphp

<div class="bg-white">
    {{-- 1. Hero (black) --------------------------------------------------- --}}
    <x-challenge.hero
        :name="$challenge->name"
        :description="$description"
        :hasDescription="$hasDescription"
        :posterImageUrl="$posterImageUrl"
        :badgeLevel="$levelData"
        :badgePuzzles="$puzzleTotal"
        :orderLabel="$orderLabel"
        :mediaImages="$heroMedia"
    />

    {{-- 2. Stats trio (white) --------------------------------------------- --}}
    <x-challenge.section eyebrow="At a glance" :bg="'white'">
        <x-challenge.stat-trio
            :puzzleTotal="$puzzleTotal"
            :orderLabel="$orderLabel"
            :timeLimit="$timeLimit"
            :priceMyr="$challenge->price_myr"
            :priceUsd="$challenge->price_usd"
        />
    </x-challenge.section>

    {{-- 3. Journey narrative (white) -------------------------------------- --}}
    @if($contentHtml !== '' || $hasDescription)
        <x-challenge.section
            id="journey"
            eyebrow="The journey"
            heading="What this challenge is about"
            sub="Step-by-step: each puzzle is a checkpoint on your way to the medal."
            :bg="'white'"
            :contained="false"
        >
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                @if($contentHtml !== '')
                    <article class="space-y-6 text-base leading-relaxed text-neutral-700">
                        {!! $contentHtml !!}
                    </article>
                @else
                    <article class="space-y-6 text-base leading-relaxed text-neutral-700">
                        {!! $description !!}
                    </article>
                @endif
            </div>
        </x-challenge.section>
    @endif

    {{-- 4. Media gallery + Videos (white) --------------------------------- --}}
    @if($imageGallery !== [] || $videos !== [])
        <x-challenge.section
            id="gallery"
            eyebrow="Inside the challenge"
            heading="A look at the puzzles"
            :bg="'white'"
        >
            <x-challenge.media-grid :images="$imageGallery" :alt="$challenge->name" />

            @if($videos !== [])
                <div class="@if($imageGallery !== []) mt-10 @endif">
                    <x-challenge.video-grid :videos="$videos" />
                </div>
            @endif
        </x-challenge.section>
    @endif

    {{-- 5. Medal showcase (BLACK — the visual centerpiece) ---------------- --}}
    @if($medalArtworkUrl || $medalImages !== [])
        <x-challenge.section
            id="medal"
            eyebrow="The prize"
            heading="The finisher's medal"
            sub="A physical medal, designed for this challenge and shipped to you when you finish."
            :bg="'dark'"
        >
            <x-challenge.medal-showcase
                :name="$challenge->name"
                :medalArtworkUrl="$medalArtworkUrl"
                :medalImages="$medalImages"
            />
        </x-challenge.section>
    @endif

    {{-- 6. Benefit grid (white) ------------------------------------------- --}}
    <x-challenge.section
        id="benefits"
        eyebrow="Plus all this"
        heading="Everything you get"
        :bg="'white'"
    >
        <x-challenge.benefit-grid />
    </x-challenge.section>

    {{-- 7. Pricing / enrollment (CHARTREUSE — the brand moment) ---------- --}}
    <x-challenge.section
        id="enroll"
        eyebrow="Enroll"
        :heading="$userEnrollment ? 'Your enrollment' : 'Join this challenge'"
        sub="One-time payment. Lifetime access to the puzzles. We ship the medal when you finish."
        :bg="'brand'"
    >
        <x-challenge.pricing-card
            :enrollUrl="$enrollUrl"
            :registerUrl="$registerUrl"
            :loginUrl="$loginUrl"
            :checkoutUrl="$checkoutUrl"
            :playUrl="$playUrl"
            :trackUrl="$trackUrl"
            :challengesUrl="$challengesUrl"
            :stickerArtworkUrl="$stickerArtworkUrl"
            :userEnrollment="$userEnrollment"
            :isGuest="$isGuest"
            :isAdmin="$isAdmin"
            variant="brand"
        />
    </x-challenge.section>

    {{-- 8. FAQ accordion (white) ----------------------------------------- --}}
    @if(true)
        <x-challenge.section
            id="faq"
            eyebrow="FAQ"
            heading="Frequently asked questions"
            :bg="'white'"
        >
            <x-challenge.faq-accordion :items="$faqItems !== [] ? $faqItems : $placeholderFaqs" />
        </x-challenge.section>
    @endif

    {{-- 9. Terms & Conditions (white) ------------------------------------ --}}
    @if($hasTerms)
        <x-challenge.section
            id="terms"
            eyebrow="Fine print"
            heading="Terms & conditions"
            :bg="'white'"
        >
            <details class="group overflow-hidden rounded-2xl bg-white ring-1 ring-neutral-900/10 shadow-warm">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-6 py-5 text-sm font-semibold text-neutral-700">
                    <span>Tap to read the full terms for this challenge</span>
                    <svg class="h-5 w-5 shrink-0 text-neutral-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div class="border-t border-neutral-100 px-6 py-6">
                    <article class="prose prose-neutral max-w-none text-base leading-relaxed">
                        {!! $termsHtml !!}
                    </article>
                </div>
            </details>
        </x-challenge.section>
    @endif

    {{-- Sticky CTA appears after scrolling past the hero ------------------ --}}
    <x-challenge.sticky-cta
        :ctaLabel="$stickyCtaLabel"
        :ctaHref="$stickyCtaHref"
        :secondaryLabel="$stickySecondaryLabel"
        :secondaryHref="$stickySecondaryHref"
        :title="$stickyTitle"
        :subtitle="$stickySubtitle"
        :showAfter="500"
    />

    <div class="h-20 sm:h-24" aria-hidden="true"></div>
</div>
