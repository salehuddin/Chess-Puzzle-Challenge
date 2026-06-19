<?php

namespace App\Console\Commands;

use App\Models\Puzzle;
use Illuminate\Console\Command;

class ImportLichessPuzzles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'puzzles:import 
                            {path? : The absolute or relative path to the Lichess CSV file (default: storage/app/lichess_db_puzzle.csv)}
                            {--min-rating= : Minimum puzzle rating to import}
                            {--max-rating= : Maximum puzzle rating to import}
                            {--min-popularity= : Minimum puzzle popularity (usually -100 to 100)}
                            {--themes=* : Only import puzzles containing ALL these themes (e.g. --themes=mateIn1 --themes=fork)}
                            {--limit= : Maximum total number of puzzles to import (useful for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingests the massive Lichess Puzzle CSV database safely without crashing memory.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path') ?: storage_path('app/lichess_db_puzzle.csv');

        if (! file_exists($path)) {
            $this->error("No CSV file found at: {$path}");
            $this->line('Please download the Lichess compressed file, extract the .csv file via 7-Zip, and place it at storage/app/lichess_db_puzzle.csv');

            return Command::FAILURE;
        }

        $minRating = $this->option('min-rating') !== null ? (int) $this->option('min-rating') : null;
        $maxRating = $this->option('max-rating') !== null ? (int) $this->option('max-rating') : null;
        $minPop = $this->option('min-popularity') !== null ? (int) $this->option('min-popularity') : null;
        $reqThemes = $this->option('themes') ?? [];
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $this->info("Opening CSV stream: {$path}");

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->error('Failed to open file stream.');

            return Command::FAILURE;
        }

        $batchSize = 2500;
        $batch = [];
        $processed = 0;
        $imported = 0;
        $skipped = 0;
        $startTime = now();

        $this->getOutput()->progressStart($limit ?? 100000); // Progress bar approximation if no limit

        // Read line by line to preserve memory
        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            // Lichess format normally: PuzzleId,FEN,Moves,Rating,RatingDeviation,Popularity,NbPlays,Themes,GameUrl,OpeningTags
            if (count($data) < 9) {
                continue;
            }

            // Skip header if exists
            if ($data[0] === 'PuzzleId') {
                continue;
            }

            $processed++;

            $lichessId = $data[0];
            $fen = $data[1];
            $movesStr = trim($data[2]);
            $rating = (int) $data[3];
            $popularity = (int) $data[5];
            $themesStr = trim($data[7]);

            // Filters
            $skip = false;
            if ($minRating !== null && $rating < $minRating) {
                $skip = true;
            }
            if ($maxRating !== null && $rating > $maxRating) {
                $skip = true;
            }
            if ($minPop !== null && $popularity < $minPop) {
                $skip = true;
            }

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
                $skipped++;

                continue;
            }

            // Map Data
            $movesArray = $movesStr ? explode(' ', $movesStr) : [];
            $themesArray = $themesStr ? explode(' ', $themesStr) : [];

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

            if (count($batch) >= $batchSize) {
                Puzzle::insertOrIgnore($batch);
                $imported += count($batch);
                $batch = [];

                // Manually advance progress bar visually
                if (! $limit || $imported <= $limit) {
                    $this->getOutput()->progressAdvance($batchSize);
                }
            }

            if ($limit !== null && $imported >= $limit) {
                break;
            }
        }

        // Insert remaining
        if (count($batch) > 0) {
            Puzzle::insertOrIgnore($batch);
            $imported += count($batch);
        }

        fclose($handle);
        $this->getOutput()->progressFinish();

        $minutes = now()->diffInMinutes($startTime);
        $seconds = now()->diffInSeconds($startTime) % 60;

        $this->info("Import Complete in {$minutes}m {$seconds}s!");
        $this->newLine();
        $this->table(
            ['Total Processed', 'Filtered Out (Skipped)', 'Successfully Imported'],
            [[$processed, $skipped, $imported]]
        );

        return Command::SUCCESS;
    }
}
