<?php

namespace App\Livewire;

use App\Services\CsvPuzzleService;
use Filament\Notifications\Notification;
use Livewire\Component;

class CsvPuzzleExplorer extends Component
{
    public string $csvPath = '';

    public ?int $minRating = null;

    public ?int $maxRating = null;

    public ?int $minPopularity = 80;

    public bool $randomMode = false;

    /** Max number of results to return (random or capped) */
    public int $limit = 100;

    public array $limitOptions = [10, 50, 100, 250, 500, 1000, 2500, 5000, 10000];

    public int $page = 1;

    public int $perPage = 50;

    public array $rows = [];

    public int $totalMatches = -1;

    public bool $hasSearched = false;

    /** Theme selection */
    public array $availableThemes = [];

    public array $selectedThemes = [];

    public bool $themesLoaded = false;

    public string $themeSearch = '';

    /** Count preview */
    public int $matchCount = -1;

    /** All random picks (only populated in random mode) */
    public array $randomPicks = [];

    public bool $streaming = false;

    protected CsvPuzzleService $csvService;

    public function boot(CsvPuzzleService $csvService): void
    {
        $this->csvService = $csvService;
    }

    public function mount(): void
    {
        $this->csvPath = storage_path('app/lichess_db_puzzle.csv');
    }

    public function getFilePath(): string
    {
        $path = trim($this->csvPath);
        if ($path === '') {
            $path = storage_path('app/lichess_db_puzzle.csv');
        }

        return $path;
    }

    public function updated($property): void
    {
        if (in_array($property, ['minRating', 'maxRating', 'minPopularity', 'selectedThemes', 'randomMode', 'limit'])) {
            $this->resetResults();
        }
    }

    public function loadThemes(): void
    {
        $path = $this->getFilePath();

        if (! file_exists($path)) {
            Notification::make()
                ->title('File not found')
                ->body("Could not find: {$path}")
                ->danger()
                ->send();

            return;
        }

        $this->streaming = true;
        $this->availableThemes = $this->csvService->getThemes($path);
        $this->themesLoaded = true;
        $this->streaming = false;

        Notification::make()
            ->title('Themes loaded')
            ->body(number_format(count($this->availableThemes)).' unique themes found.')
            ->success()
            ->send();
    }

    public function toggleTheme(string $theme): void
    {
        if (in_array($theme, $this->selectedThemes)) {
            $this->selectedThemes = array_values(array_diff($this->selectedThemes, [$theme]));
        } else {
            $this->selectedThemes[] = $theme;
        }

        $this->resetResults();
    }

    public function countMatches(): void
    {
        $path = $this->getFilePath();

        if (! file_exists($path)) {
            Notification::make()
                ->title('File not found')
                ->body("Could not find: {$path}")
                ->danger()
                ->send();

            return;
        }

        $this->streaming = true;
        $filters = $this->buildFilters();
        $this->matchCount = $this->csvService->countMatches($path, $filters);
        $this->streaming = false;
    }

    public function applyFilters(): void
    {
        $path = $this->getFilePath();

        if (! file_exists($path)) {
            Notification::make()
                ->title('File not found')
                ->body("Could not find: {$path}")
                ->danger()
                ->send();

            return;
        }

        $this->page = 1;
        $this->streaming = true;
        $this->hasSearched = true;

        $filters = $this->buildFilters();
        $limit = max(1, (int) $this->limit);

        if ($this->randomMode) {
            $this->randomPicks = $this->csvService->pickRandom($path, $filters, $limit);
            $this->totalMatches = count($this->randomPicks);
            $this->sliceRandomPage();
        } else {
            $this->randomPicks = [];
            $result = $this->csvService->getPage($path, $filters, $this->page, $this->perPage, $limit);
            $this->rows = $result['rows'];
            $this->totalMatches = $result['total'];
        }

        $this->streaming = false;
    }

    public function goToPage(int $page): void
    {
        $path = $this->getFilePath();

        if (! file_exists($path)) {
            return;
        }

        $this->page = max(1, $page);
        $this->streaming = true;

        if ($this->randomMode && ! empty($this->randomPicks)) {
            $totalPages = max(1, (int) ceil(count($this->randomPicks) / $this->perPage));
            if ($this->page > $totalPages) {
                $this->page = $totalPages;
            }
            $this->sliceRandomPage();
        } else {
            $filters = $this->buildFilters();
            $limit = max(1, (int) $this->limit);
            $result = $this->csvService->getPage($path, $filters, $this->page, $this->perPage, $limit);
            $this->rows = $result['rows'];
            $this->totalMatches = $result['total'];
        }

        $this->streaming = false;
    }

    public function exportCsv(): void
    {
        $path = $this->getFilePath();

        if (! file_exists($path)) {
            Notification::make()->title('File not found')->danger()->send();

            return;
        }

        $timestamp = now()->format('Ymd_His');
        $exportPath = storage_path("app/exports/puzzles_{$timestamp}.csv");
        $filters = $this->buildFilters();

        try {
            if ($this->randomMode && ! empty($this->randomPicks)) {
                $count = $this->csvService->exportRows($this->randomPicks, $exportPath);
            } else {
                $count = $this->csvService->exportFiltered($path, $filters, $exportPath, (int) $this->limit);
            }

            Notification::make()
                ->title('Export complete')
                ->body("{$count} puzzles exported to storage/app/exports/puzzles_{$timestamp}.csv")
                ->success()
                ->send();
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('Export failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function importToDb(): void
    {
        $path = $this->getFilePath();

        if (! file_exists($path)) {
            Notification::make()->title('File not found')->danger()->send();

            return;
        }

        set_time_limit(0);
        $this->streaming = true;

        try {
            if ($this->randomMode && ! empty($this->randomPicks)) {
                $result = $this->csvService->importRows($this->randomPicks);
            } else {
                $filters = $this->buildFilters();
                $result = $this->csvService->importFiltered($path, $filters, (int) $this->limit);
            }

            $imported = $result['imported'];
            $skipped = $result['skipped'];

            Notification::make()
                ->title('Import complete')
                ->body("{$imported} puzzles imported. {$skipped} duplicates skipped.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->streaming = false;
    }

    public function resetFilters(): void
    {
        $this->minRating = null;
        $this->maxRating = null;
        $this->minPopularity = 80;
        $this->selectedThemes = [];
        $this->randomMode = false;
        $this->limit = 100;
        $this->resetResults();
    }

    public function render()
    {
        return view('livewire.csv-puzzle-explorer');
    }

    public function getTotalPagesProperty(): int
    {
        return max(1, (int) ceil($this->totalMatches / $this->perPage));
    }

    public function getFilteredThemesProperty(): array
    {
        if ($this->themeSearch === '') {
            return $this->availableThemes;
        }

        return array_values(array_filter(
            $this->availableThemes,
            fn ($theme) => stripos($theme, $this->themeSearch) !== false
        ));
    }

    private function buildFilters(): array
    {
        return [
            'min_rating' => $this->minRating,
            'max_rating' => $this->maxRating,
            'min_popularity' => $this->minPopularity,
            'themes' => implode(',', $this->selectedThemes),
        ];
    }

    private function resetResults(): void
    {
        $this->rows = [];
        $this->totalMatches = -1;
        $this->matchCount = -1;
        $this->page = 1;
        $this->randomPicks = [];
        $this->hasSearched = false;
    }

    private function sliceRandomPage(): void
    {
        $offset = ($this->page - 1) * $this->perPage;
        $this->rows = array_slice($this->randomPicks, $offset, $this->perPage);
    }
}
