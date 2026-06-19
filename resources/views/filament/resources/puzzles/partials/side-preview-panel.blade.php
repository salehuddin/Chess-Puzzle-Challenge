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
        <div style="border-radius: 12px; border: 1px solid rgba(17, 24, 39, 0.08); background: #ffffff; padding: 20px; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);">
            <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 700; color: #111827;">Puzzle Preview</h3>
            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #6b7280;">Click a puzzle row in the table to load its preview here.</p>
        </div>
    </template>

    <template x-if="selected">
        <div style="max-width: 100%; overflow: visible; border-radius: 12px; background: #ffffff; padding: 12px; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); border: 1px solid rgba(17, 24, 39, 0.08);">
            <h3 style="margin: 0 0 6px; font-size: 18px; line-height: 1.2; font-weight: 700; color: #111827;" x-text="'Puzzle ' + selected.lichessId"></h3>

            <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 8px;">
                <span style="display: inline-block; border-radius: 9999px; padding: 2px 10px; font-size: 12px; font-weight: 700; background: #f3f4f6; color: #111827;" x-text="'Rating ' + formatRating(selected.rating)"></span>

                <template x-for="theme in selected.themes" :key="theme">
                    <span style="display: inline-flex; align-items: center; border-radius: 9999px; padding: 3px 8px; font-size: 11px; font-weight: 600; background: #e5e7eb; color: #1f2937;" x-text="theme"></span>
                </template>
            </div>

            <div style="margin-bottom: 8px; border-radius: 8px; border: 1px solid #e5e7eb; background: #ffffff; padding: 8px;">
                <div style="margin: 0 0 4px; font-size: 11px; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280; font-weight: 700;">FEN</div>
                <code style="display: block; overflow-x: auto; white-space: nowrap; font-size: 11px; color: #111827;" x-text="selected.fen"></code>
            </div>

            <p style="margin: 0; font-size: 12px; line-height: 1.4; color: #6b7280;">Interact with the board exactly as a user would. This environment is isolated and does not trigger subscription completion markers.</p>

            <div style="position: relative; margin-top: 12px; display: flex; flex-direction: row; gap: 16px; align-items: flex-start; min-height: 0;">
                <div style="position: relative; width: 320px; max-width: 320px; flex: 0 0 320px; margin: 0 auto;">
                    <div style="position: relative; width: 320px; height: 320px; border-radius: 10px; border: 4px solid #f3f4f6; background: #ffffff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); overflow: hidden;">
                        <div id="board-preview-client" x-ref="board" wire:ignore class="w-full h-full"></div>
                    </div>
                </div>

                <div style="min-height: 0; flex: 1 1 auto; overflow: auto; border-radius: 10px; border: 1px solid #e5e7eb; background: #f9fafb; padding: 16px;">
                    <div x-show="!ready" style="color: #9ca3af; font-weight: 500;">Loading puzzle UI...</div>
                    <div x-show="ready" x-cloak class="w-full text-center">
                        <p style="margin: 0 0 12px; color: #374151; font-size: 14px;">
                            Find the best move for
                            <span style="display: inline-block; margin-left: 6px; border-radius: 6px; padding: 2px 8px; font-size: 12px; font-weight: 700;"
                                  :class="playerColor === 'white' ? 'bg-amber-100 text-amber-900' : 'bg-gray-800 text-white'"
                                  x-text="playerColor === 'white' ? 'White' : 'Black'">
                            </span>
                        </p>
                        <template x-if="lastOpponentMove">
                            <div class="p-3 bg-white rounded border border-gray-200 shadow-sm w-full mx-auto max-w-[200px]">
                                <div class="flex items-center justify-center gap-2 text-gray-500 mb-1">
                                    <span class="w-2 h-2 rounded-full bg-red-400 inline-block animate-pulse"></span>
                                    <span class="text-xs uppercase tracking-widest font-bold">Opponent Played</span>
                                </div>
                                <div class="font-mono text-xl font-bold text-gray-900" x-text="lastOpponentMove"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
