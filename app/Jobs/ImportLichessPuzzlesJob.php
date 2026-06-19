<?php

namespace App\Jobs;

use App\Models\Puzzle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportLichessPuzzlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour max

    public function __construct(
        public string $path,
        public array $filters = []
    ) {}

    public function handle(): void
    {
        if (! file_exists($this->path)) {
            Log::error("ImportLichessPuzzlesJob: File not found at {$this->path}");

            return;
        }

        $minRating = $this->filters['min_rating'] ?? null;
        $maxRating = $this->filters['max_rating'] ?? null;
        $minPop = $this->filters['min_popularity'] ?? null;

        $reqThemes = [];
        if (! empty($this->filters['themes'])) {
            $reqThemes = array_map('trim', explode(',', $this->filters['themes']));
        }

        $limit = $this->filters['limit'] ?? null;

        Log::info("ImportLichessPuzzlesJob started streaming: {$this->path}");

        $handle = fopen($this->path, 'r');
        if (! $handle) {
            Log::error("ImportLichessPuzzlesJob: Failed to open stream for {$this->path}");

            return;
        }

        $batchSize = 2500;
        $batch = [];
        $imported = 0;
        $skipped = 0;
        $seenInStream = [];

        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            if (count($data) < 9 || $data[0] === 'PuzzleId') {
                continue;
            }

            $lichessId = $data[0];
            $fen = $data[1];
            $movesStr = trim($data[2]);
            $rating = (int) $data[3];
            $popularity = (int) $data[5];
            $themesStr = trim($data[7]);

            $skip = false;
            // Numerical gating
            if ($minRating !== null && $rating < $minRating) {
                $skip = true;
            }
            if ($maxRating !== null && $rating > $maxRating) {
                $skip = true;
            }
            if ($minPop !== null && $popularity < $minPop) {
                $skip = true;
            }

            // Theme verification
            if (! $skip && ! empty($reqThemes)) {
                $puzzleThemes = explode(' ', $themesStr);
                foreach ($reqThemes as $reqTheme) {
                    if (! in_array($reqTheme, $puzzleThemes)) {
                        $skip = true;
                        break;
                    }
                }
            }

            if ($skip) {
                continue;
            }

            if (isset($seenInStream[$lichessId])) {
                $skipped++;
                if ($limit !== null && ($imported + $skipped) >= $limit) {
                    break;
                }

                continue;
            }
            $seenInStream[$lichessId] = true;

            $movesArray = $movesStr !== '' ? explode(' ', $movesStr) : [];
            $themesArray = $themesStr !== '' ? explode(' ', $themesStr) : [];

            $batch[] = [
                'lichess_id' => $lichessId,
                'fen' => $fen,
                'moves' => json_encode($movesArray),
                'rating' => $rating,
                'rating_deviation' => (int) $data[4],
                'popularity' => $popularity,
                'nb_plays' => (int) $data[6],
                'themes' => json_encode($themesArray),
                'game_url' => $data[8],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize || ($limit !== null && ($imported + $skipped + count($batch)) >= $limit)) {
                $result = $this->insertBatch($batch);
                $imported += $result['imported'];
                $skipped += $result['skipped'];
                $batch = [];
            }

            if ($limit !== null && ($imported + $skipped) >= $limit) {
                break;
            }
        }

        // Catch residuals
        if (count($batch) > 0) {
            $result = $this->insertBatch($batch);
            $imported += $result['imported'];
            $skipped += $result['skipped'];
        }

        fclose($handle);
        Log::info("ImportLichessPuzzlesJob: Imported {$imported} puzzles, skipped {$skipped} duplicates.");
    }

    /**
     * Insert a batch of rows, skipping any lichess_ids already in the database.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function insertBatch(array $rows): array
    {
        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0];
        }

        $existingIds = Puzzle::query()
            ->whereIn('lichess_id', array_column($rows, 'lichess_id'))
            ->pluck('lichess_id')
            ->all();

        $newRows = [];
        $skipped = 0;
        foreach ($rows as $row) {
            if (in_array($row['lichess_id'], $existingIds, true)) {
                $skipped++;

                continue;
            }
            $newRows[] = $row;
        }

        if (! empty($newRows)) {
            Puzzle::insert($newRows);
        }

        return ['imported' => count($newRows), 'skipped' => $skipped];
    }
}
