import puzzlePlayer from './puzzle-player';

function puzzlePreviewPanel(initialPuzzle = null) {
    return {
        ...puzzlePlayer(),
        selected: initialPuzzle,

        init() {
            this.$nextTick(() => {
                if (this.selected) {
                    this.loadSelectedPuzzle(this.selected);
                    this.applyRowHighlight(this.selected.id);
                }
            });

            window.addEventListener('puzzle-preview-select', (event) => {
                const payload = event.detail;
                if (!payload?.id || !payload?.fen || !payload?.moves) {
                    return;
                }

                this.loadSelectedPuzzle(payload);
                this.applyRowHighlight(payload.id);
                this.syncPreviewQuery(payload.id);
            });
        },

        loadSelectedPuzzle(payload) {
            this.selected = payload;
            this.ready = false;

            this.$nextTick(() => {
                this.initPlayer(
                    payload.fen,
                    payload.moves,
                    payload.lichessId,
                    null,
                    false,
                    '',
                    'board-preview-client',
                    0,
                );
            });
        },

        applyRowHighlight(id) {
            const rowClassList = ['cpc-preview-row'];

            document.querySelectorAll('.fi-ta-row, .fi-ta-record').forEach((row) => {
                row.classList.remove(...rowClassList);
            });

            const sourceCell = document.querySelector(`[data-preview-id="${String(id)}"]`);
            sourceCell?.closest('.fi-ta-row, .fi-ta-record')?.classList.add(...rowClassList);
        },

        syncPreviewQuery(id) {
            const url = new URL(window.location.href);
            url.searchParams.set('preview', String(id));
            window.history.replaceState({}, '', url);
        },

        formatRating(value) {
            const num = Number(value ?? 0);

            return Number.isFinite(num) ? num.toLocaleString() : '0';
        },
    };
}

function parsePreviewPayloadFromElement(element) {
    if (!element) {
        return null;
    }

    const themesRaw = atob(element.getAttribute('data-preview-themes') ?? btoa('[]'));
    let themes = [];

    try {
        themes = JSON.parse(themesRaw);
    } catch {
        themes = [];
    }

    const movesRaw = atob(element.getAttribute('data-preview-moves') ?? btoa('[]'));

    return {
        id: Number(element.getAttribute('data-preview-id')),
        lichessId: element.getAttribute('data-preview-lichess-id') ?? '',
        fen: element.getAttribute('data-preview-fen') ?? '',
        moves: movesRaw,
        rating: Number(element.getAttribute('data-preview-rating') ?? 0),
        themes: Array.isArray(themes) ? themes : [],
    };
}

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    // Only react when the click originates from the preview payload cell itself.
    const source = event.target.closest('[data-preview-id]');
    if (!source) {
        return;
    }

    const payload = parsePreviewPayloadFromElement(source);
    if (!payload?.id || !payload.fen || !payload.moves) {
        return;
    }

    // Only stop the default navigation – don't block sorting, filters, etc.
    event.preventDefault();

    window.dispatchEvent(new CustomEvent('puzzle-preview-select', {
        detail: payload,
    }));
});

if (window.Alpine) {
    window.Alpine.data('puzzlePlayer', puzzlePlayer);
    window.Alpine.data('puzzlePreviewPanel', puzzlePreviewPanel);
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('puzzlePlayer', puzzlePlayer);
        window.Alpine.data('puzzlePreviewPanel', puzzlePreviewPanel);
    });
}
