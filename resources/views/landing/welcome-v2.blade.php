<x-landing-layout>
    <x-slot name="title">Chess Puzzle Challenge — Solve 100 Puzzles. Earn a Real Medal.</x-slot>
    <x-slot name="description">Work through 100 curated Lichess puzzles. Complete the series and earn a custom-designed physical medal shipped to your door — plus a digital sticker for your Hall of Fame.</x-slot>

    {{-- Scroll-reveal Intersection Observer script (reused from welcome.blade.php) --}}
    @push('scripts')
        <script>
            (function() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -60px 0px'
                });

                const init = () => {
                    document.querySelectorAll('.reveal, .reveal-scale, .reveal-left, .reveal-right').forEach((el) => {
                        observer.observe(el);
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }

                document.addEventListener('livewire:navigated', init);
            })();
        </script>
    @endpush

    @include('landing.sections.01-promo')
    @include('landing.sections.02-hero')
    @include('landing.sections.03-trust')
    @include('landing.sections.04-stats')
    @include('landing.sections.05-themes-marquee')
    @include('landing.sections.06-how-it-works')
    @include('landing.sections.07-block-puzzles')
    @include('landing.sections.08-block-reward')
    @include('landing.sections.09-challenges-cards', ['challenges' => $challenges])
    @include('landing.sections.10-content-grid', ['bundles' => $bundles])
    @include('landing.sections.11-testimonials')
    @include('landing.sections.12-faq')
    @include('landing.sections.13-final-cta')
</x-landing-layout>
