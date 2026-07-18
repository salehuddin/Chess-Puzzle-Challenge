import confetti from 'canvas-confetti';

function challengeComplete() {
    return {
        showReviewCard: false,
        reviewSubmitted: false,
        selectedPuzzleRating: 0,
        selectedPlatformRating: 0,
        hoverPuzzleRating: 0,
        hoverPlatformRating: 0,

        init() {
            this.fireConfetti();
            window.addEventListener('review-submitted', () => {
                this.reviewSubmitted = true;
                this.showReviewCard = false;
                this.fireThankYou();
            });
        },

        selectPuzzleRating(value) {
            this.selectedPuzzleRating = value;
            this.showReviewCard = true;
            const root = this.$refs.reviewCard;
            if (root) {
                this.$nextTick(() => root.scrollIntoView({ behavior: 'smooth', block: 'center' }));
            }
        },

        selectPlatformRating(value) {
            this.selectedPlatformRating = value;
            this.$wire.platformRating = value;
        },

        fireConfetti() {
            const colors = ['#B7FF00', '#F59E0B', '#FFFFFF', '#1F2937'];
            confetti({
                particleCount: 80,
                spread: 70,
                origin: { y: 0.6 },
                colors,
                disableForReducedMotion: true,
            });
            setTimeout(() => {
                confetti({
                    particleCount: 50,
                    angle: 60,
                    spread: 60,
                    origin: { x: 0, y: 0.65 },
                    colors,
                    disableForReducedMotion: true,
                });
                confetti({
                    particleCount: 50,
                    angle: 120,
                    spread: 60,
                    origin: { x: 1, y: 0.65 },
                    colors,
                    disableForReducedMotion: true,
                });
            }, 250);
        },

        fireThankYou() {
            const colors = ['#B7FF00', '#F59E0B'];
            confetti({
                particleCount: 40,
                spread: 50,
                origin: { y: 0.7 },
                colors,
                disableForReducedMotion: true,
            });
        },

        async submitReview() {
            this.$wire.puzzleRating = this.selectedPuzzleRating;
            this.$wire.platformRating = this.selectedPlatformRating;
            await this.$wire.call('submitReview');
        },

        copyShareLink() {
            const url = window.location.href;
            navigator.clipboard?.writeText(url).then(() => {
                this.$dispatch('copied', { url });
            });
        },
    };
}

window.challengeComplete = challengeComplete;

if (window.Alpine) {
    window.Alpine.data('challengeComplete', challengeComplete);
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('challengeComplete', challengeComplete);
    });
}
