import { Chessground } from 'chessground';
import { Chess } from 'chess.js';

export default function puzzlePlayer() {
    return {
        fen: null,
        moves: [],
        currentMoveIndex: 0,
        puzzleId: null,
        completionToken: null,
        isFinalPuzzle: false,
        
        chess: null,
        board: null,
        
        showSuccess: false,
        showError: false,
        lastMoveError: '',
        ready: false,
        playerColor: 'white',
        lastOpponentMove: '',
        resizeObserver: null,
        pendingOpponentTimer: null,

        hintSquare: null,
        hintClicks: 0,
        hintCountedForCurrentPosition: false,

        lastValidFen: null,

        subscriptionId: null,

        puzzleError: false,
        puzzleErrorMessage: '',

        getStorageKey() {
            const subId = this.subscriptionId
                ?? window.location.pathname.split('/').pop()
                ?? 'unknown';
            return `chess-puzzle-state-${subId}-${this.puzzleId}`;
        },

        saveState() {
            if (!this.puzzleId || !this.chess) return;
            try {
                localStorage.setItem(this.getStorageKey(), JSON.stringify({
                    puzzleId: this.puzzleId,
                    fen: this.chess.fen(),
                    currentMoveIndex: this.currentMoveIndex,
                    playerColor: this.playerColor,
                    isFinalPuzzle: this.isFinalPuzzle,
                    completionToken: this.completionToken,
                    lastOpponentMove: this.lastOpponentMove,
                    savedAt: Date.now(),
                }));
            } catch (e) { /* quota exceeded, silently ignore */ }
        },

        restoreState() {
            try {
                const raw = localStorage.getItem(this.getStorageKey());
                if (!raw) return null;
                const state = JSON.parse(raw);
                if (Date.now() - state.savedAt > 86400000) {
                    localStorage.removeItem(this.getStorageKey());
                    return null;
                }
                if (state.puzzleId !== this.puzzleId) return null;
                return state;
            } catch {
                return null;
            }
        },

        applyRestoredState(state) {
            this.currentMoveIndex = state.currentMoveIndex;
            this.playerColor = state.playerColor;
            this.isFinalPuzzle = state.isFinalPuzzle ?? this.isFinalPuzzle;
            this.completionToken = state.completionToken ?? this.completionToken;
            this.lastOpponentMove = state.lastOpponentMove ?? '';

            this.chess = new Chess(this.fen);

            for (let i = 0; i < state.currentMoveIndex; i++) {
                const moveToken = this.moves[i];
                if (!moveToken) continue;
                const moveObj = this.uciToMoveObj(String(moveToken))
                    ?? this.resolveExpectedMove(String(moveToken));
                if (moveObj) {
                    try { this.chess.move(moveObj); } catch { /* skip */ }
                }
            }

            this.board.set({
                fen: this.chess.fen(),
                orientation: this.playerColor,
                turnColor: this.playerColor,
                movable: {
                    color: this.playerColor,
                    dests: this.getDests(),
                },
                drawable: this.getDrawable(),
            });

            localStorage.removeItem(this.getStorageKey());
        },

        clearSavedState() {
            try {
                localStorage.removeItem(this.getStorageKey());
            } catch { /* ignore */ }
        },

        initPlayer(fen, movesArrayStr, id, completionToken = null, isFinalPuzzle = false, boardElementId = 'board', initialOpponentDelay = 600) {
            // Cancel any previously-scheduled opponent auto-move. This prevents the
            // double-fire bug where initPlayer is called twice (x-init + puzzle-loaded).
            if (this.pendingOpponentTimer !== null) {
                clearTimeout(this.pendingOpponentTimer);
                this.pendingOpponentTimer = null;
            }

            this.puzzleError = false;
            this.puzzleErrorMessage = '';
            this.fen = fen;
            this.moves = this.normalizeMoves(movesArrayStr);
            this.puzzleId = id;
            this.completionToken = completionToken;
            this.isFinalPuzzle = Boolean(isFinalPuzzle);
            this.hintSquare = null;
            this.hintClicks = 0;
            this.hintCountedForCurrentPosition = false;

            if (!this.fen || !this.moves) return;

            // Validate all moves at load time — fail fast instead of silently skipping.
            const validation = this.validateMoves(this.fen, this.moves);
            if (!validation.valid) {
                this.puzzleError = true;
                this.puzzleErrorMessage = validation.error;
                console.error('[PuzzlePlayer] Puzzle data validation failed:', validation.error);
                this.ready = true;
                return;
            }

            this.currentMoveIndex = 0;
            this.showSuccess = false;
            this.showError = false;
            this.lastMoveError = '';
            this.lastOpponentMove = '';
            
            this.chess = new Chess(this.fen);

            // Lichess puzzle format: the side-to-move in the FEN is the OPPONENT who
            // plays moves[0] (the triggering move). The PLAYER is the opposite color
            // and responds from moves[1] onward.
            this.playerColor = this.chess.turn() === 'w' ? 'black' : 'white';

            const config = {
                fen: this.fen,
                orientation: this.playerColor,
                // Board starts with opponent to move; disable player pieces until
                // the opponent's auto-played first move completes.
                turnColor: this.chess.turn() === 'w' ? 'white' : 'black',
                movable: {
                    color: undefined,
                    free: false,
                    dests: new Map(),
                    events: {
                        after: this.onHumanMove.bind(this)
                    }
                }
            };
            
            const boardEl = this.$refs?.board
                ?? document.getElementById(boardElementId)
                ?? document.getElementById('board');
            if (boardEl) {
                if (this.resizeObserver) {
                    this.resizeObserver.disconnect();
                    this.resizeObserver = null;
                }

                // Check if Chessground is already mounted on this element (persisted
                // across Livewire re-renders via wire:ignore). If so, update in place
                // for a smooth transition — no board flash, no destroy/create cycle.
                const existingCg = boardEl._chessgroundInstance;
                if (existingCg) {
                    this.board = existingCg;
                    this.board.set(config);
                    this.ready = true;
                    this.installResizeObserver(boardEl);
                    this.reflowBoard();

                    // Check for persisted progress from a previous tab/refresh.
                    const saved = this.restoreState();
                    if (saved && saved.currentMoveIndex > 0) {
                        this.applyRestoredState(saved);
                    } else {
                        this.clearSavedState();
                        if (initialOpponentDelay > 0) {
                            this.pendingOpponentTimer = setTimeout(() => {
                                this.pendingOpponentTimer = null;
                                this.playOpponentMove();
                            }, initialOpponentDelay);
                        } else {
                            this.playOpponentMove();
                        }
                    }
                } else {
                    const mountBoard = () => {
                        this.board = Chessground(boardEl, config);
                        boardEl._chessgroundInstance = this.board;
                        this.ready = true;
                        this.installResizeObserver(boardEl);
                        this.reflowBoard();

                        // Check for persisted progress from a previous tab/refresh.
                        const saved = this.restoreState();
                        if (saved && saved.currentMoveIndex > 0) {
                            this.applyRestoredState(saved);
                        } else {
                            this.clearSavedState();
                            if (initialOpponentDelay > 0) {
                                this.pendingOpponentTimer = setTimeout(() => {
                                    this.pendingOpponentTimer = null;
                                    this.playOpponentMove();
                                }, initialOpponentDelay);
                            } else {
                                this.playOpponentMove();
                            }
                        }
                    };

                    this.waitForBoardSize(boardEl, mountBoard);
                }
            }
        },

        normalizeMoves(rawMoves) {
            let parsed = rawMoves;

            if (typeof parsed === 'string') {
                try {
                    parsed = JSON.parse(parsed);
                } catch (e) {
                    parsed = parsed.split(/[\s,]+/).filter(Boolean);
                }
            }

            if (!Array.isArray(parsed)) {
                return [];
            }

            return parsed
                .map((move) => String(move).trim())
                .filter((move) => move.length >= 4);
        },

        validateMoves(fen, moves) {
            if (!moves || moves.length === 0) {
                return { valid: false, error: 'No moves in puzzle sequence.' };
            }

            const chess = new Chess(fen);

            for (let i = 0; i < moves.length; i++) {
                const moveToken = String(moves[i]).trim();
                let moved = false;

                // Try UCI first
                const uciObj = this.uciToMoveObj(moveToken);
                if (uciObj) {
                    try {
                        const result = chess.move(uciObj);
                        if (result) { moved = true; }
                    } catch (e) { /* fall through to SAN */ }
                }

                // Try SAN as fallback
                if (!moved) {
                    try {
                        const result = chess.move(moveToken);
                        if (result) { moved = true; }
                    } catch (e) { /* both failed */ }
                }

                if (!moved) {
                    return {
                        valid: false,
                        error: `Puzzle data error: move ${i + 1} ("${moveToken}") is invalid in the current position.`,
                    };
                }
            }

            return { valid: true };
        },

        isLegalMove(moveObj) {
            const legalMoves = this.chess.moves({ verbose: true });

            return legalMoves.some((move) => move.from === moveObj.from && move.to === moveObj.to && (!moveObj.promotion || move.promotion === moveObj.promotion));
        },

        resolveExpectedMove(expectedMove) {
            if (typeof expectedMove !== 'string') {
                return null;
            }

            const normalized = expectedMove.trim();
            if (!normalized) {
                return null;
            }

            const uciMove = this.uciToMoveObj(normalized);
            if (uciMove) {
                return uciMove;
            }

            if (!this.chess) {
                return null;
            }

            try {
                const previewChess = new Chess(this.chess.fen());
                const parsedMove = previewChess.move(normalized);

                if (!parsedMove) {
                    return null;
                }

                return {
                    from: parsedMove.from,
                    to: parsedMove.to,
                    promotion: parsedMove.promotion ?? undefined,
                };
            } catch {
                return null;
            }
        },

        moveObjectsMatch(leftMove, rightMove) {
            if (!leftMove || !rightMove) {
                return false;
            }

            return leftMove.from === rightMove.from
                && leftMove.to === rightMove.to
                && (leftMove.promotion ?? undefined) === (rightMove.promotion ?? undefined);
        },

        waitForBoardSize(boardEl, callback, attempts = 0) {
            const width = boardEl.clientWidth;
            const height = boardEl.clientHeight;

            if (width > 40 && height > 40) {
                callback();

                return;
            }

            if (attempts > 30) {
                callback();

                return;
            }

            requestAnimationFrame(() => {
                this.waitForBoardSize(boardEl, callback, attempts + 1);
            });
        },

        installResizeObserver(boardEl) {
            if (!window.ResizeObserver || !this.board) {
                return;
            }

            this.resizeObserver = new ResizeObserver(() => {
                this.reflowBoard();
            });

            this.resizeObserver.observe(boardEl);
        },

        reflowBoard() {
            if (!this.board) {
                return;
            }

            if (typeof this.board.redrawAll === 'function') {
                this.board.redrawAll();

                return;
            }

            if (typeof this.board.redraw === 'function') {
                this.board.redraw();

                return;
            }

            this.board.set({
                fen: this.chess?.fen() ?? this.fen,
                drawable: this.getDrawable(),
            });
        },

        getDests() {
            const dests = new Map();
            this.chess.moves({ verbose: true }).forEach(m => {
                let d = dests.get(m.from);
                if (d) d.push(m.to);
                else dests.set(m.from, [m.to]);
            });
            return dests;
        },

        getAutoShapes() {
            if (!this.hintSquare) return [];
            return [{ orig: this.hintSquare, brush: 'yellow' }];
        },

        getDrawable() {
            return {
                enabled: true,
                visible: true,
                autoShapes: this.getAutoShapes(),
            };
        },

        showEngineHint() {
            if (!this.chess || this.currentMoveIndex >= this.moves.length || this.puzzleError) return;

            // Only hint when it's the player's turn (after the opponent has moved).
            const currentTurn = this.chess.turn();
            const playerTurn = this.playerColor === 'white' ? 'w' : 'b';
            if (currentTurn !== playerTurn) return;

            const expectedMoveToken = this.moves[this.currentMoveIndex];
            const expectedMoveObj = this.resolveExpectedMove(expectedMoveToken);
            if (!expectedMoveObj) return;

            this.hintSquare = expectedMoveObj.from;

            // Only count one hint per position so spam-clicking doesn't inflate it.
            if (!this.hintCountedForCurrentPosition) {
                this.hintClicks++;
                this.hintCountedForCurrentPosition = true;
            }

            this.board.set({ drawable: this.getDrawable() });
        },

        clearEngineHint() {
            this.hintSquare = null;
            this.hintCountedForCurrentPosition = false;
            if (this.board) {
                this.board.set({ drawable: { autoShapes: [] } });
            }
        },

        playOpponentMove() {
            if (this.currentMoveIndex >= this.moves.length) return;

            this.clearEngineHint();

            const moveToken = this.moves[this.currentMoveIndex];
            // Resolve opponent move using the same flexible resolver (UCI or SAN).
            const moveObj = this.resolveExpectedMove(moveToken);

            if (!moveObj || !this.isLegalMove(moveObj)) {
                this.puzzleError = true;
                this.puzzleErrorMessage = `Puzzle data error: expected move ${this.currentMoveIndex + 1} ("${moveToken}") cannot be played.`;
                console.error('[PuzzlePlayer] Cannot play opponent move', { index: this.currentMoveIndex, token: moveToken });
                return;
            }
            
            this.lastValidFen = this.chess.fen();
            
            try {
                const moveResult = this.chess.move(moveObj);
                if (moveResult) {
                    this.lastOpponentMove = moveResult.san;
                    this.currentMoveIndex++;
                    this.saveState();
                    
                    this.board.set({
                        fen: this.chess.fen(),
                        turnColor: this.playerColor,
                        lastMove: [moveResult.from, moveResult.to]
                    });

                    // If this was the last move in the sequence (trailing opponent
                    // response after player's final move), show success.
                    if (this.currentMoveIndex >= this.moves.length) {
                        this.board.set({ movable: { color: undefined } });
                        setTimeout(() => { this.showSuccess = true; }, 800);
                    } else {
                        this.board.set({ movable: { dests: this.getDests(), color: this.playerColor } });
                    }
                }
            } catch (e) {
                this.puzzleError = true;
                this.puzzleErrorMessage = `Puzzle data error: move ${this.currentMoveIndex} failed to execute.`;
                console.error('[PuzzlePlayer] Move execution failed', e);
            }
        },

        onHumanMove(orig, dest, metadata) {
            if (this.currentMoveIndex >= this.moves.length) return;

            this.clearEngineHint();

            const expectedMoveToken = this.moves[this.currentMoveIndex];
            const expectedMoveObj = this.resolveExpectedMove(expectedMoveToken);

            if (!expectedMoveObj) {
                this.puzzleError = true;
                this.puzzleErrorMessage = `Puzzle data error: expected move ${this.currentMoveIndex + 1} cannot be parsed.`;
                console.error('[PuzzlePlayer] Cannot resolve expected move', { index: this.currentMoveIndex, token: expectedMoveToken });
                return;
            }

            let moveUci = orig + dest;

            // Detect pawn promotion. Chessground does not provide the promotion
            // piece in the `after` callback, so we infer it from the puzzle's
            // expected move (defaulting to queen when the puzzle data lacks it).
            const movedPiece = this.chess.get(orig);
            const isPromotion = movedPiece
                && movedPiece.type === 'p'
                && (
                    (movedPiece.color === 'w' && dest.charAt(1) === '8')
                    || (movedPiece.color === 'b' && dest.charAt(1) === '1')
                );

            if (isPromotion) {
                const expectedPromotion = typeof expectedMoveToken === 'string' && expectedMoveToken.length === 5
                    ? expectedMoveToken.charAt(4).toLowerCase()
                    : null;
                moveUci += expectedPromotion || 'q';
            }

            const humanMoveObj = this.uciToMoveObj(moveUci);

            if (!humanMoveObj) {
                this.showError = true;
                return;
            }
            
            if (this.moveObjectsMatch(humanMoveObj, expectedMoveObj)) {
                try {
                    this.chess.move(humanMoveObj);
                    this.currentMoveIndex++;
                    this.saveState();
                    
                    this.board.set({
                        fen: this.chess.fen(),
                        lastMove: [orig, dest]
                    });
                    
                    if (this.currentMoveIndex >= this.moves.length) {
                        this.board.set({ movable: { color: undefined } });
                        setTimeout(() => {
                            this.showSuccess = true;
                        }, 400);
                    } else {
                        this.board.set({ movable: { color: undefined } });
                        setTimeout(() => {
                            this.playOpponentMove();
                        }, 500);
                    }
                } catch (e) {
                    console.error("Move execution failed", e);
                }
            } else {
                this.showError = true;
                this.lastMoveError = this.buildMoveHint(orig, expectedMoveObj);
            }
        },

        buildMoveHint(humanFrom, expectedMoveObj) {
            if (!this.chess) return '';

            const piece = this.chess.get(expectedMoveObj.from);
            if (!piece) return '';

            const names = { k: 'King', q: 'Queen', r: 'Rook', b: 'Bishop', n: 'Knight', p: 'Pawn' };
            const pieceName = names[piece.type] || 'piece';

            if (humanFrom === expectedMoveObj.from) {
                const promo = expectedMoveObj.promotion ? ` (promote to ${names[expectedMoveObj.promotion] ?? expectedMoveObj.promotion})` : '';
                return `Your ${pieceName} from ${expectedMoveObj.from} should go to ${expectedMoveObj.to}${promo}.`;
            }

            return `Look at the ${pieceName} on ${expectedMoveObj.from}.`;
        },

        retryMove() {
            this.showError = false;
            this.lastMoveError = '';
            this.clearEngineHint();
            this.board.set({
                fen: this.chess.fen(),
                turnColor: this.playerColor,
                movable: { dests: this.getDests(), color: this.playerColor },
                drawable: this.getDrawable(),
            });
        },

        undoMove() {
            if (!this.chess || !this.board || this.currentMoveIndex <= 1) {
                // currentMoveIndex <= 1 means we are at or before the player's first
                // turn (opponent's opening move already played). Nothing to undo.
                return;
            }

            this.showError = false;
            this.lastMoveError = '';
            this.showSuccess = false;
            this.clearEngineHint();

            const playerTurn = this.playerColor === 'white' ? 'w' : 'b';

            // Undo until it becomes the player's turn again (one full round).
            while (this.currentMoveIndex > 1) {
                const undone = this.chess.undo();
                if (!undone) {
                    break;
                }

                this.currentMoveIndex--;

                if (this.chess.turn() === playerTurn) {
                    break;
                }
            }

            this.lastOpponentMove = '';
            this.syncBoardState();
            this.saveState();
        },

        resetPuzzle() {
            if (!this.fen) {
                return;
            }

            this.clearSavedState();

            this.initPlayer(
                this.fen,
                this.moves,
                this.puzzleId,
                this.completionToken,
                this.isFinalPuzzle,
                'board',
                0,
            );
        },

        syncBoardState() {
            if (!this.board || !this.chess) {
                return;
            }

            this.board.set({
                fen: this.chess.fen(),
                turnColor: this.playerColor,
                movable: {
                    color: this.playerColor,
                    dests: this.getDests(),
                },
                drawable: this.getDrawable(),
            });
        },

        nextPuzzle(wire) {
            // Keep success modal visible during the Livewire round trip so the user
            // doesn't see the old board flash. The new component instance will have
            // showSuccess=false by default after re-render.
            this.clearSavedState();
            if (this.isFinalPuzzle) {
                wire.completeChallenge(this.completionToken);

                return;
            }

            wire.solvePuzzle(this.puzzleId, this.completionToken);
        },
        
        uciToMoveObj(uci) {
            if (typeof uci !== 'string') {
                return null;
            }

            const normalized = uci.trim().toLowerCase();
            if (!/^[a-h][1-8][a-h][1-8][qrbn]?$/.test(normalized)) {
                return null;
            }

            return {
                from: normalized.substring(0, 2),
                to: normalized.substring(2, 4),
                promotion: normalized.length === 5 ? normalized[4] : undefined
            };
        }
    }
}
