@php
    $previewPuzzle = $this->getPreviewPuzzle();
    $initialPuzzlePayload = $previewPuzzle
        ? [
            'id' => (int) $previewPuzzle->id,
            'lichessId' => (string) $previewPuzzle->lichess_id,
            'fen' => (string) $previewPuzzle->fen,
            'moves' => is_array($previewPuzzle->moves) ? json_encode($previewPuzzle->moves) : (string) $previewPuzzle->moves,
            'rating' => (int) ($previewPuzzle->rating ?? 0),
            'themes' => is_array($previewPuzzle->themes) ? $previewPuzzle->themes : [],
        ]
        : null;
@endphp

@vite(['resources/css/filament-puzzle-preview.css', 'resources/js/filament-puzzle-preview.js'])

<div style="position: sticky; top: 1rem;" x-data="puzzlePreviewPanel(@js($initialPuzzlePayload))" x-init="init()">
    <template x-if="!selected">
        <div class="cpc-preview-panel cpc-preview-panel--empty">
            <h3 class="cpc-preview-panel__title">Puzzle Preview</h3>
            <p class="cpc-preview-panel__hint">Click a puzzle row in the table to load its preview here.</p>
        </div>
    </template>

    <template x-if="selected">
        <div class="cpc-preview-panel">
            <h3 class="cpc-preview-panel__title" x-text="'Puzzle ' + selected.lichessId"></h3>

            <div class="cpc-preview-panel__chips">
                <span class="cpc-preview-panel__chip cpc-preview-panel__chip--rating" x-text="'Rating ' + formatRating(selected.rating)"></span>

                <template x-for="theme in selected.themes" :key="theme">
                    <span class="cpc-preview-panel__chip" x-text="theme"></span>
                </template>
            </div>

            <div class="cpc-preview-panel__fen">
                <div class="cpc-preview-panel__fen-label">FEN</div>
                <code class="cpc-preview-panel__fen-code" x-text="selected.fen"></code>
            </div>

            <p class="cpc-preview-panel__hint">Interact with the board exactly as a user would. This environment is isolated and does not trigger subscription completion markers.</p>

            <div class="cpc-preview-panel__board-row">
                <div class="cpc-preview-panel__board-frame">
                    <div id="board-preview-client" x-ref="board" wire:ignore class="w-full h-full"></div>
                </div>

                <div class="cpc-preview-panel__side">
                    <div x-show="!ready" class="cpc-preview-panel__loading">Loading puzzle UI...</div>
                    <div x-show="ready" x-cloak class="w-full text-center">
                        <p class="cpc-preview-panel__turn">
                            Find the best move for
                            <span class="cpc-preview-panel__turn-chip"
                                  :class="playerColor === 'white' ? 'cpc-preview-panel__turn-chip--white' : 'cpc-preview-panel__turn-chip--black'"
                                  x-text="playerColor === 'white' ? 'White' : 'Black'">
                            </span>
                        </p>
                        <template x-if="lastOpponentMove">
                            <div class="cpc-preview-panel__opponent">
                                <div class="cpc-preview-panel__opponent-label">
                                    <span class="cpc-preview-panel__opponent-dot"></span>
                                    <span>Opponent Played</span>
                                </div>
                                <div class="cpc-preview-panel__opponent-move" x-text="lastOpponentMove"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
