<?php

namespace App\Filament\Resources\Puzzles\Support;

use App\Models\Puzzle;
use Illuminate\Support\Str;

class PuzzleThemes
{
    /**
     * Common Lichess puzzle themes as a label-keyed array for use in filters/selects.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'advantage' => 'Advantage',
            'anastasiaMate' => 'Anastasia Mate',
            'arabianMate' => 'Arabian Mate',
            'attraction' => 'Attraction',
            'backRankMate' => 'Back Rank Mate',
            'bishopEndgame' => 'Bishop Endgame',
            'bodenMate' => 'Boden\'s Mate',
            'capturingDefender' => 'Capturing Defender',
            'clearance' => 'Clearance',
            'crushing' => 'Crushing',
            'defensiveMove' => 'Defensive Move',
            'deflection' => 'Deflection',
            'discoveredAttack' => 'Discovered Attack',
            'doubleCheck' => 'Double Check',
            'endgame' => 'Endgame',
            'enPassant' => 'En Passant',
            'equality' => 'Equality',
            'exposedKing' => 'Exposed King',
            'fork' => 'Fork',
            'hangingPiece' => 'Hanging Piece',
            'hookMate' => 'Hook Mate',
            'interference' => 'Interference',
            'intermezzo' => 'Intermezzo',
            'kingsideAttack' => 'Kingside Attack',
            'knightEndgame' => 'Knight Endgame',
            'long' => 'Long',
            'master' => 'Master Game',
            'mate' => 'Mate',
            'mateIn1' => 'Mate in 1',
            'mateIn2' => 'Mate in 2',
            'mateIn3' => 'Mate in 3',
            'mateIn4' => 'Mate in 4',
            'mateIn5' => 'Mate in 5',
            'middlegame' => 'Middlegame',
            'oneMove' => 'One Move',
            'opening' => 'Opening',
            'pawnEndgame' => 'Pawn Endgame',
            'pin' => 'Pin',
            'queenEndgame' => 'Queen Endgame',
            'queenRookEndgame' => 'Queen + Rook Endgame',
            'queensideAttack' => 'Queenside Attack',
            'quietMove' => 'Quiet Move',
            'rookEndgame' => 'Rook Endgame',
            'sacrifice' => 'Sacrifice',
            'short' => 'Short',
            'skewer' => 'Skewer',
            'smotheredMate' => 'Smothered Mate',
            'superGM' => 'Super GM',
            'trappedPiece' => 'Trapped Piece',
            'underPromotion' => 'Under-Promotion',
            'veryLong' => 'Very Long',
            'xRayAttack' => 'X-Ray Attack',
            'zugzwang' => 'Zugzwang',
        ];
    }

    /**
     * Theme options derived from the themes actually stored on puzzles.
     *
     * Labels are taken from the hard-coded map when available, otherwise
     * generated from the theme key.
     *
     * @return array<string, string>
     */
    public static function availableOptions(): array
    {
        $themeLabels = static::options();

        $usedThemes = Puzzle::query()
            ->whereNotNull('themes')
            ->pluck('themes')
            ->flatten()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $options = [];
        foreach ($usedThemes as $theme) {
            $options[(string) $theme] = $themeLabels[(string) $theme] ?? Str::headline((string) $theme);
        }

        return $options;
    }
}
