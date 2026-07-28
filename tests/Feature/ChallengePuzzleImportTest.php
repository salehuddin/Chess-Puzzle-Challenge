<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Puzzle;
use App\Services\CsvPuzzleService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChallengePuzzleImportTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function writeCsv(string $filename, array $rows): string
    {
        Storage::fake('local');
        Storage::disk('local')->makeDirectory('puzzle-uploads');

        $handle = tmpfile();
        fputcsv($handle, ['PuzzleId', 'FEN', 'Moves', 'Rating', 'RatingDeviation', 'Popularity', 'NbPlays', 'Themes', 'GameUrl']);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        $relativePath = "puzzle-uploads/{$filename}";
        $absolutePath = Storage::disk('local')->path($relativePath);

        rewind($handle);
        copy(stream_get_meta_data($handle)['uri'], $absolutePath);
        fclose($handle);

        return $absolutePath;
    }

    public function test_fresh_csv_imports_into_pool_and_attaches_with_gapless_sequence(): void
    {
        $challenge = Challenge::factory()->create();
        $service = new CsvPuzzleService;

        $path = $this->writeCsv('fresh.csv', [
            ['abc01', 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21', 'a1a2 b2b3', 1500, 80, 90, 200, 'fork pin', 'https://lichess.org/abc01'],
            ['abc02', '8/8/8/8/3Q4/8/3K4/8 w - - 0 1', 'a1a2', 1800, 70, 85, 150, 'endgame', 'https://lichess.org/abc02'],
            ['abc03', 'r1bqk2r/pppp1ppp/2n2n2/4p3/1bB1P3/2NP1N2/PPP2PPP/R1BQK2R b KQkq - 0 5', 'a1a2 b2b3 c3c4', 2000, 90, 95, 500, 'opening middlegame', 'https://lichess.org/abc03'],
        ]);

        $result = $service->importRowsForChallenge($path, [], $challenge->id);

        $this->assertSame(3, $result['imported_into_pool']);
        $this->assertSame(3, $result['attached_to_challenge']);
        $this->assertSame(0, $result['skipped_already_attached']);

        $this->assertSame(3, $challenge->puzzles()->count());

        $sequences = $challenge->puzzles()
            ->orderBy('challenge_puzzle.sequence')
            ->pluck('challenge_puzzle.sequence')
            ->all();
        $this->assertSame([1, 2, 3], $sequences);
    }

    public function test_rerunning_same_csv_does_not_double_import_or_attach(): void
    {
        $challenge = Challenge::factory()->create();
        $service = new CsvPuzzleService;

        $rows = [
            ['xyz01', 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21', 'a1a2', 1500, 80, 90, 200, 'fork', 'https://lichess.org/xyz01'],
            ['xyz02', '8/8/8/8/3Q4/8/3K4/8 w - - 0 1', 'a1a2', 1800, 70, 85, 150, 'endgame', 'https://lichess.org/xyz02'],
        ];
        $path = $this->writeCsv('rerun.csv', $rows);

        $first = $service->importRowsForChallenge($path, [], $challenge->id);
        $this->assertSame(2, $first['imported_into_pool']);
        $this->assertSame(2, $first['attached_to_challenge']);
        $this->assertSame(0, $first['skipped_already_attached']);

        $second = $service->importRowsForChallenge($path, [], $challenge->id);
        $this->assertSame(0, $second['imported_into_pool'], 'No new puzzle should be inserted into the pool');
        $this->assertSame(0, $second['attached_to_challenge'], 'No new puzzle should be attached');
        $this->assertSame(2, $second['skipped_already_attached'], 'Both puzzles were already attached');

        $this->assertSame(2, $challenge->puzzles()->count());
        $this->assertSame(2, Puzzle::query()->count());
    }

    public function test_csv_with_puzzle_already_in_pool_but_unattached_attaches_without_reimporting(): void
    {
        $challenge = Challenge::factory()->create();

        Puzzle::query()->create([
            'lichess_id' => 'pre01',
            'fen' => 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21',
            'moves' => ['a1a2'],
            'rating' => 1500,
            'rating_deviation' => 80,
            'popularity' => 90,
            'nb_plays' => 200,
            'themes' => ['fork'],
            'game_url' => 'https://lichess.org/pre01',
        ]);

        $service = new CsvPuzzleService;

        $path = $this->writeCsv('mixed.csv', [
            ['pre01', 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21', 'a1a2', 1500, 80, 90, 200, 'fork', 'https://lichess.org/pre01'],
            ['new02', '8/8/8/8/3Q4/8/3K4/8 w - - 0 1', 'a1a2', 1800, 70, 85, 150, 'endgame', 'https://lichess.org/new02'],
        ]);

        $result = $service->importRowsForChallenge($path, [], $challenge->id);

        $this->assertSame(1, $result['imported_into_pool'], 'Only new02 should be inserted');
        $this->assertSame(2, $result['attached_to_challenge'], 'Both pre01 and new02 should attach');
        $this->assertSame(0, $result['skipped_already_attached']);

        $this->assertSame(2, $challenge->puzzles()->count());
        $this->assertSame(2, Puzzle::query()->count(), 'Pool should not have grown past the 2 distinct lichess_ids');
    }

    public function test_csv_with_puzzle_already_attached_is_skipped_not_double_counted(): void
    {
        $challenge = Challenge::factory()->create();

        $attached = Puzzle::query()->create([
            'lichess_id' => 'att01',
            'fen' => 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21',
            'moves' => ['a1a2'],
            'rating' => 1500,
            'rating_deviation' => 80,
            'popularity' => 90,
            'nb_plays' => 200,
            'themes' => ['fork'],
            'game_url' => 'https://lichess.org/att01',
        ]);
        $challenge->puzzles()->attach([$attached->id => ['sequence' => 1]]);

        $service = new CsvPuzzleService;

        $path = $this->writeCsv('already.csv', [
            ['att01', 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21', 'a1a2', 1500, 80, 90, 200, 'fork', 'https://lichess.org/att01'],
            ['new02', '8/8/8/8/3Q4/8/3K4/8 w - - 0 1', 'a1a2', 1800, 70, 85, 150, 'endgame', 'https://lichess.org/new02'],
        ]);

        $result = $service->importRowsForChallenge($path, [], $challenge->id);

        $this->assertSame(1, $result['imported_into_pool'], 'Only new02 should be inserted');
        $this->assertSame(1, $result['attached_to_challenge'], 'Only new02 should attach');
        $this->assertSame(1, $result['skipped_already_attached'], 'att01 was already attached');

        $this->assertSame(2, $challenge->puzzles()->count());

        $sequences = $challenge->puzzles()
            ->orderBy('challenge_puzzle.sequence')
            ->pluck('challenge_puzzle.sequence')
            ->all();
        $this->assertSame([1, 2], $sequences, 'Sequences should remain gapless after the run');
    }

    public function test_empty_csv_is_a_noop(): void
    {
        $challenge = Challenge::factory()->create();
        $service = new CsvPuzzleService;

        $path = $this->writeCsv('empty.csv', []);

        $result = $service->importRowsForChallenge($path, [], $challenge->id);

        $this->assertSame(0, $result['imported_into_pool']);
        $this->assertSame(0, $result['attached_to_challenge']);
        $this->assertSame(0, $result['skipped_already_attached']);
        $this->assertSame(0, $challenge->puzzles()->count());
        $this->assertSame(0, Puzzle::query()->count());
    }

    public function test_filters_apply_during_challenge_import(): void
    {
        $challenge = Challenge::factory()->create();
        $service = new CsvPuzzleService;

        $path = $this->writeCsv('filtered.csv', [
            ['fil01', 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21', 'a1a2', 1200, 80, 90, 200, 'fork', 'https://lichess.org/fil01'],
            ['fil02', '8/8/8/8/3Q4/8/3K4/8 w - - 0 1', 'a1a2', 1800, 70, 85, 150, 'endgame', 'https://lichess.org/fil02'],
            ['fil03', 'r1bqk2r/pppp1ppp/2n2n2/4p3/1bB1P3/2NP1N2/PPP2PPP/R1BQK2R b KQkq - 0 5', 'a1a2', 2500, 90, 95, 500, 'opening', 'https://lichess.org/fil03'],
        ]);

        $result = $service->importRowsForChallenge($path, ['min_rating' => 1500, 'max_rating' => 2000], $challenge->id);

        $this->assertSame(1, $result['imported_into_pool']);
        $this->assertSame(1, $result['attached_to_challenge']);

        $this->assertTrue($challenge->puzzles()->where('lichess_id', 'fil02')->exists());
        $this->assertFalse($challenge->puzzles()->where('lichess_id', 'fil01')->exists());
        $this->assertFalse($challenge->puzzles()->where('lichess_id', 'fil03')->exists());
    }

    public function test_duplicate_lichess_ids_within_same_csv_are_deduped_in_stream(): void
    {
        $challenge = Challenge::factory()->create();
        $service = new CsvPuzzleService;

        $path = $this->writeCsv('dup.csv', [
            ['dup01', 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21', 'a1a2', 1500, 80, 90, 200, 'fork', 'https://lichess.org/dup01'],
            ['dup01', 'r6k/pp3rpp/4Rp2/8/8/8/PPPP1PPP/5RK1 b - - 0 21', 'a1a2', 1500, 80, 90, 200, 'fork', 'https://lichess.org/dup01'],
        ]);

        $result = $service->importRowsForChallenge($path, [], $challenge->id);

        $this->assertSame(1, $result['imported_into_pool']);
        $this->assertSame(1, $result['attached_to_challenge']);

        $this->assertSame(1, $challenge->puzzles()->count());
        $this->assertSame(1, Puzzle::query()->where('lichess_id', 'dup01')->count());
    }
}
