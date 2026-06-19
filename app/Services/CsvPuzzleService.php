<?php

namespace App\Services;

use App\Models\Puzzle;
use Generator;
use RuntimeException;

class CsvPuzzleService
{
    const EXPECTED_COLUMNS = 9;

    public function streamMatches(string $path, array $filters = []): Generator
    {
        $minRating = $filters['min_rating'] ?? null;
        $maxRating = $filters['max_rating'] ?? null;
        $minPopularity = $filters['min_popularity'] ?? null;
        $reqThemes = $this->parseThemes($filters['themes'] ?? '');

        $handle = fopen($path, 'r');
        if (! $handle) {
            return;
        }

        $matchIndex = -1;

        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            if (count($data) < self::EXPECTED_COLUMNS || $data[0] === 'PuzzleId') {
                continue;
            }

            $rating = (int) $data[3];
            $popularity = (int) $data[5];
            $themesStr = trim($data[7]);

            if ($minRating !== null && $rating < $minRating) {
                continue;
            }
            if ($maxRating !== null && $rating > $maxRating) {
                continue;
            }
            if ($minPopularity !== null && $popularity < $minPopularity) {
                continue;
            }

            if (! empty($reqThemes)) {
                $puzzleThemes = explode(' ', $themesStr);
                $hasAll = true;
                foreach ($reqThemes as $reqTheme) {
                    if (! in_array($reqTheme, $puzzleThemes)) {
                        $hasAll = false;
                        break;
                    }
                }
                if (! $hasAll) {
                    continue;
                }
            }

            $matchIndex++;

            yield [
                'match_index' => $matchIndex,
                'lichess_id' => $data[0],
                'fen' => $data[1],
                'moves' => trim($data[2]),
                'rating' => $rating,
                'rating_deviation' => (int) $data[4],
                'popularity' => $popularity,
                'nb_plays' => (int) $data[6],
                'themes' => $themesStr,
                'game_url' => $data[8],
            ];
        }

        fclose($handle);
    }

    /**
     * Scan the CSV and return a sorted list of unique themes.
     */
    public function getThemes(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $themes = [];

        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            if (count($data) < self::EXPECTED_COLUMNS || $data[0] === 'PuzzleId') {
                continue;
            }

            $themesStr = trim($data[7]);
            if ($themesStr === '') {
                continue;
            }

            foreach (explode(' ', $themesStr) as $theme) {
                $theme = trim($theme);
                if ($theme !== '') {
                    $themes[$theme] = true;
                }
            }
        }

        fclose($handle);

        $themes = array_keys($themes);
        sort($themes, SORT_NATURAL | SORT_FLAG_CASE);

        return $themes;
    }

    /**
     * Import rows directly into the puzzles table.
     *
     * @return array<string, int> ['imported' => int, 'skipped' => int]
     */
    public function importRows(array $rows): array
    {
        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0];
        }

        $imported = 0;
        $skipped = 0;
        $batchSize = 2500;

        $chunks = array_chunk($rows, $batchSize);
        foreach ($chunks as $chunk) {
            $dedupedChunk = $this->deduplicateRows($chunk, $skipped);
            $result = $this->insertBatch($dedupedChunk);
            $imported += $result['imported'];
            $skipped += $result['skipped'];
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Stream filtered rows directly into the puzzles table.
     *
     * @return array<string, int> ['imported' => int, 'skipped' => int]
     */
    public function importFiltered(string $path, array $filters, ?int $limit = null): array
    {
        $imported = 0;
        $skipped = 0;
        $batch = [];
        $batchSize = 2500;
        $seenInStream = [];

        foreach ($this->streamMatches($path, $filters) as $row) {
            $id = $row['lichess_id'];

            if (isset($seenInStream[$id])) {
                $skipped++;
                if ($limit !== null && ($imported + $skipped) >= $limit) {
                    break;
                }

                continue;
            }
            $seenInStream[$id] = true;

            $batch[] = $row;

            if (count($batch) >= $batchSize) {
                $result = $this->insertBatch($batch);
                $imported += $result['imported'];
                $skipped += $result['skipped'];
                $batch = [];
            }

            if ($limit !== null && ($imported + $skipped) >= $limit) {
                break;
            }
        }

        if (! empty($batch) && ($limit === null || ($imported + $skipped) < $limit)) {
            $result = $this->insertBatch($batch);
            $imported += $result['imported'];
            $skipped += $result['skipped'];
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    public function countMatches(string $path, array $filters = []): int
    {
        $count = 0;
        foreach ($this->streamMatches($path, $filters) as $_) {
            $count++;
        }

        return $count;
    }

    public function getPage(string $path, array $filters, int $page, int $perPage = 50, ?int $limit = null): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = [];
        $total = 0;
        $hasEnoughRows = false;

        foreach ($this->streamMatches($path, $filters) as $row) {
            $total++;

            if ($limit !== null && $total > $limit) {
                $total = $limit;
                break;
            }

            if ($total <= $offset) {
                continue;
            }

            if (! $hasEnoughRows) {
                $rows[] = $row;
                if (count($rows) >= $perPage) {
                    $hasEnoughRows = true;
                    if ($limit === null) {
                        break;
                    }
                }
            }
        }

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }

    /**
     * Reservoir sampling: pick K random rows from the filtered stream.
     * Single pass, O(N) time, O(K) memory.
     */
    public function pickRandom(string $path, array $filters, int $k): array
    {
        $reservoir = [];
        $i = 0;

        foreach ($this->streamMatches($path, $filters) as $row) {
            if ($i < $k) {
                $reservoir[$i] = $row;
            } else {
                $j = random_int(0, $i);
                if ($j < $k) {
                    $reservoir[$j] = $row;
                }
            }
            $i++;
        }

        return array_values($reservoir);
    }

    public function exportFiltered(string $path, array $filters, string $outputPath, ?int $limit = null): int
    {
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $outHandle = fopen($outputPath, 'w');
        if (! $outHandle) {
            throw new RuntimeException("Cannot write to {$outputPath}");
        }

        fputcsv($outHandle, [
            'PuzzleId', 'FEN', 'Moves', 'Rating', 'RatingDeviation',
            'Popularity', 'NbPlays', 'Themes', 'GameUrl',
        ]);

        $written = 0;
        foreach ($this->streamMatches($path, $filters) as $row) {
            fputcsv($outHandle, [
                $row['lichess_id'],
                $row['fen'],
                $row['moves'],
                $row['rating'],
                $row['rating_deviation'],
                $row['popularity'],
                $row['nb_plays'],
                $row['themes'],
                $row['game_url'],
            ]);
            $written++;

            if ($limit !== null && $written >= $limit) {
                break;
            }
        }

        fclose($outHandle);

        return $written;
    }

    public function exportRows(array $rows, string $outputPath): int
    {
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $outHandle = fopen($outputPath, 'w');
        if (! $outHandle) {
            throw new RuntimeException("Cannot write to {$outputPath}");
        }

        fputcsv($outHandle, [
            'PuzzleId', 'FEN', 'Moves', 'Rating', 'RatingDeviation',
            'Popularity', 'NbPlays', 'Themes', 'GameUrl',
        ]);

        foreach ($rows as $row) {
            fputcsv($outHandle, [
                $row['lichess_id'],
                $row['fen'],
                $row['moves'],
                $row['rating'],
                $row['rating_deviation'],
                $row['popularity'],
                $row['nb_plays'],
                $row['themes'],
                $row['game_url'],
            ]);
        }

        fclose($outHandle);

        return count($rows);
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
            Puzzle::insert(array_map(fn (array $row): array => $this->buildInsertRow($row), $newRows));
        }

        return ['imported' => count($newRows), 'skipped' => $skipped];
    }

    /**
     * Deduplicate rows within a chunk by lichess_id (keeps the first occurrence).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  int  $skippedCounter  Passed by reference and incremented for duplicates.
     * @return array<int, array<string, mixed>>
     */
    private function deduplicateRows(array $rows, int &$skippedCounter): array
    {
        $seen = [];
        $deduped = [];

        foreach ($rows as $row) {
            $id = $row['lichess_id'];
            if (isset($seen[$id])) {
                $skippedCounter++;

                continue;
            }
            $seen[$id] = true;
            $deduped[] = $row;
        }

        return $deduped;
    }

    /**
     * Build a database insert row from a CSV stream row.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildInsertRow(array $row): array
    {
        $movesStr = trim((string) ($row['moves'] ?? ''));
        $themesStr = trim((string) ($row['themes'] ?? ''));

        return [
            'lichess_id' => $row['lichess_id'],
            'fen' => $row['fen'],
            'moves' => json_encode($movesStr !== '' ? explode(' ', $movesStr) : []),
            'rating' => $row['rating'],
            'rating_deviation' => $row['rating_deviation'],
            'popularity' => $row['popularity'],
            'nb_plays' => $row['nb_plays'],
            'themes' => json_encode($themesStr !== '' ? explode(' ', $themesStr) : []),
            'game_url' => $row['game_url'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function parseThemes(string $themes): array
    {
        $themes = trim($themes);
        if ($themes === '') {
            return [];
        }

        return array_map('trim', explode(',', $themes));
    }
}
