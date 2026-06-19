<?php

namespace Database\Factories;

use App\Models\Puzzle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Puzzle>
 */
class PuzzleFactory extends Factory
{
    /**
     * All valid Lichess puzzle themes (verbatim from the Lichess database).
     *
     * @var array<string>
     */
    protected static array $themes = [
        'fork', 'pin', 'skewer', 'discoveredAttack', 'doubleCheck',
        'mate', 'mateIn1', 'mateIn2', 'mateIn3', 'mateIn4', 'mateIn5',
        'rookEndgame', 'queenEndgame', 'pawnEndgame', 'knightEndgame', 'bishopEndgame',
        'opening', 'middlegame', 'endgame',
        'advantage', 'crushing', 'equality',
        'attraction', 'deflection', 'interference', 'overloading',
        'sacrifice', 'kingsideAttack', 'queensideAttack',
        'backRankMate', 'smotheredMate', 'anastasiasMate', 'arabianMate',
        'hangingPiece', 'trappedPiece', 'promotion', 'underPromotion',
        'quietMove', 'zugzwang', 'clearance', 'capturingDefender',
        'long', 'short', 'veryLong',
        'master', 'masterVsMaster',
    ];

    /**
     * Curated pool of realistic mid/end-game FEN positions.
     * Format: position one move before the puzzle begins (opponent to move).
     * These represent common tactical motif setups seen in the Lichess database.
     *
     * @var array<string>
     */
    protected static array $positions = [
        // Back-rank mate setup (black to move, drops a piece, white mates)
        'r6k/pp3rpp/4Rp2/3p4/8/1N1P4/PqP2PPP/5RK1 b - - 0 21',
        // Fork: knight fork on king and rook after blunder
        'r2qk2r/ppp2ppp/2np1n2/2b1p3/2B1P1b1/2NP1N2/PPP2PPP/R1BQ1RK1 b kq - 0 7',
        // Pin along d-file wins material
        'r1bq1rk1/ppp2ppp/2np1n2/2b1p3/2B1P3/2NP1N2/PPP2PPP/R1BQR1K1 b - - 0 8',
        // Discovered attack: bishop reveals rook attack
        'r1bqr1k1/ppp2ppp/2np4/2b1p3/4P1n1/2NP1N2/PPP1BPPP/R1BQR1K1 b - - 3 9',
        // Skewer: queen skewers king to win rook
        '6k1/5ppp/3r4/8/2Q5/8/5PPP/6K1 b - - 0 1',
        // Smothered mate: knight delivers checkmate
        'r2qkbnr/ppp2ppp/2np4/4p3/2B1P1b1/5N2/PPPP1PPP/RNBQ1RK1 b kq - 2 5',
        // Trapped piece: bishop is stuck and won
        'r1bqk2r/pppp1ppp/2n2n2/4p3/1bB1P3/2NP1N2/PPP2PPP/R1BQK2R b KQkq - 0 5',
        // Hanging piece: undefended rook lost to tactic
        'r1bq1rk1/pp3ppp/2np1n2/2p1p3/4P3/2NP1N1P/PPP2PP1/R1BQKB1R w KQ - 0 9',
        // Pawn promotion: queening wins
        '8/3P4/8/8/8/8/k5K1/8 w - - 0 1',
        // Queen endgame: queen and king vs king
        '8/8/8/4k3/3Q4/8/3K4/8 w - - 0 1',
        // Rook endgame: Lucena position
        '1K1k4/1P6/8/8/8/8/r7/2R5 w - - 0 1',
        // King and pawn endgame
        '8/8/3k4/8/3K4/8/3P4/8 w - - 0 1',
        // Double check and mate
        'r4rk1/ppp2p1p/3p2p1/q3p3/4P3/2NPbN2/PPP2PPP/R2QK2R b KQ - 3 12',
        // Attraction sacrifice: king lured to bad square
        'r3r1k1/pp3ppp/2p1bn2/8/3P4/2PB1N2/PP3PPP/R3R1K1 b - - 0 16',
        // Deflection: defender pulled away
        'r2qr1k1/pp3ppp/2p1bn2/8/3P4/2PB1N2/PP3PPP/R2QR1K1 b - - 2 15',
        // Queenside attack breakthrough
        'r1bq1rk1/1pp2ppp/p1np1n2/4p3/2PPP3/2N5/PP3PPP/R1BQKBNR b KQ d3 0 7',
        // Overloading: defender has too many duties
        'r1bqr1k1/ppp2ppp/2np1n2/4p3/4P3/2NP1N2/PPP2PPP/R1BQR1K1 b - - 0 9',
        // Clearance: piece moved to open line
        '2rq1rk1/pp1b1ppp/2np1n2/4p3/4P3/2NP1N2/PPP1BPPP/R2QR1K1 b - - 4 11',
        // Interference: blocking a key defensive piece
        'r2qr1k1/pp1b1ppp/2np1n2/4p3/4PP2/2NP1N2/PPP1B1PP/R2QR1K1 b - f3 0 12',
        // Zugzwang: any move worsens the position
        '8/8/p7/Pp6/1P6/8/6K1/7k w - - 0 1',
    ];

    /**
     * Curated pools of UCI move sequences matching each position above.
     * Format: first move = opponent's "trigger" move, rest = solution moves.
     *
     * @var array<array<string>>
     */
    protected static array $moveSets = [
        ['q b2 f2', 'e6 e7', 'f7 f6', 'e7 e8'],  // placeholder: generated per position
    ];

    /**
     * Generate the Lichess-style 5-character alphanumeric puzzle ID.
     */
    protected function lichessId(): string
    {
        return Str::of('')
            ->append(fake()->regexify('[0-9a-zA-Z]{5}'))
            ->toString();
    }

    /**
     * Generate a plausible sequence of UCI moves (e.g. "e2e4 e7e5 d1h5").
     * First move is the opponent's trigger move; the rest are the solution.
     *
     * @return array<string>
     */
    protected function uciMoves(): array
    {
        $squares = [
            'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8',
            'b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8',
            'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8',
            'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7', 'd8',
            'e1', 'e2', 'e3', 'e4', 'e5', 'e6', 'e7', 'e8',
            'f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'f7', 'f8',
            'g1', 'g2', 'g3', 'g4', 'g5', 'g6', 'g7', 'g8',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8',
        ];

        // 2–6 moves total (odd = solution ends on solver's move)
        $count = fake()->randomElement([2, 2, 3, 3, 4, 4, 5, 6]);
        $moves = [];

        for ($i = 0; $i < $count; $i++) {
            $from = fake()->randomElement($squares);

            do {
                $to = fake()->randomElement($squares);
            } while ($to === $from);

            $moves[] = $from.$to;
        }

        return $moves;
    }

    /**
     * Pick 1–3 realistic Lichess themes for a puzzle.
     *
     * @return array<string>
     */
    protected function puzzleThemes(): array
    {
        $count = fake()->numberBetween(1, 3);
        $pool = static::$themes;
        shuffle($pool);

        return array_slice($pool, 0, $count);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gameId = fake()->regexify('[a-zA-Z0-9]{8}');
        $moveNumber = fake()->numberBetween(5, 35);

        return [
            'lichess_id' => $this->lichessId(),
            'fen' => fake()->randomElement(static::$positions),
            'moves' => $this->uciMoves(),
            'rating' => fake()->numberBetween(500, 2900),
            'rating_deviation' => fake()->numberBetween(50, 300),
            'popularity' => fake()->numberBetween(-100, 100),
            'nb_plays' => fake()->numberBetween(0, 100_000),
            'themes' => $this->puzzleThemes(),
            'game_url' => "https://lichess.org/{$gameId}#{$moveNumber}",
        ];
    }

    /**
     * Puzzles suitable for beginners (rating 600–1200).
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(600, 1200),
            'themes' => fake()->randomElements(['mateIn1', 'mateIn2', 'fork', 'hangingPiece', 'short'], 2),
        ]);
    }

    /**
     * Puzzles suitable for intermediate players (rating 1200–1800).
     */
    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(1200, 1800),
            'themes' => fake()->randomElements(['fork', 'pin', 'skewer', 'discoveredAttack', 'middlegame', 'sacrifice'], 3),
        ]);
    }

    /**
     * Puzzles suitable for advanced players (rating 1800–2500).
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(1800, 2500),
            'themes' => fake()->randomElements(['long', 'veryLong', 'zugzwang', 'quietMove', 'interference', 'deflection', 'endgame'], 3),
        ]);
    }
}
