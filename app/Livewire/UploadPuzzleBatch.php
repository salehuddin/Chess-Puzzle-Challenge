<?php

namespace App\Livewire;

use App\Services\CsvPuzzleService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadPuzzleBatch extends Component
{
    use WithFileUploads;

    public $csvFile = null;

    public ?string $storedPath = null;

    public ?string $storedName = null;

    public ?int $storedSize = null;

    public array $sampleRows = [];

    public int $totalRows = -1;

    public bool $imported = false;

    public int $importedCount = 0;

    public int $skippedCount = 0;

    protected CsvPuzzleService $csvService;

    public function boot(CsvPuzzleService $csvService): void
    {
        $this->csvService = $csvService;
    }

    public function rules(): array
    {
        return [
            'csvFile' => 'required|file|extensions:csv,txt|max:5120',
        ];
    }

    protected function messages(): array
    {
        return [
            'csvFile.required' => 'Please select a CSV file to upload.',
            'csvFile.extensions' => 'Only CSV files are supported.',
            'csvFile.max' => 'The file must be 5MB or smaller.',
        ];
    }

    public function updatedCsvFile(): void
    {
        if ($this->storedPath && file_exists($this->storedPath)) {
            @unlink($this->storedPath);
        }

        $this->reset([
            'storedPath', 'storedName', 'storedSize',
            'sampleRows', 'totalRows',
            'imported', 'importedCount', 'skippedCount',
        ]);
        $this->resetErrorBag();

        $this->validateOnly('csvFile');

        $relative = $this->csvFile->store('puzzle-uploads', 'local');

        $this->storedPath = Storage::disk('local')->path($relative);
        $this->storedName = $this->csvFile->getClientOriginalName();
        $this->storedSize = $this->csvFile->getSize();

        $this->buildPreview();
    }

    public function buildPreview(): void
    {
        if (! $this->storedPath || ! file_exists($this->storedPath)) {
            return;
        }

        try {
            $result = $this->csvService->getPage($this->storedPath, [], 1, 5, null);

            $this->sampleRows = $result['rows'];
            $this->totalRows = $result['total'];
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Preview failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function importToDb(): void
    {
        if (! $this->storedPath || ! file_exists($this->storedPath)) {
            Notification::make()->title('No file ready to import')->danger()->send();

            return;
        }

        $this->imported = false;
        set_time_limit(0);

        try {
            $result = $this->csvService->importFiltered($this->storedPath, [], null);

            $this->importedCount = $result['imported'];
            $this->skippedCount = $result['skipped'];
            $this->imported = true;

            Notification::make()
                ->title('Import complete')
                ->body("{$this->importedCount} puzzles imported. {$this->skippedCount} duplicates skipped.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearFile(): void
    {
        if ($this->storedPath && file_exists($this->storedPath)) {
            @unlink($this->storedPath);
        }

        $this->reset([
            'csvFile', 'storedPath', 'storedName', 'storedSize',
            'sampleRows', 'totalRows',
            'imported', 'importedCount', 'skippedCount',
        ]);
        $this->resetErrorBag();
    }

    public function getFormattedSizeProperty(): string
    {
        if (! $this->storedSize) {
            return '';
        }

        $kb = $this->storedSize / 1024;

        if ($kb < 1024) {
            return number_format($kb, 1).' KB';
        }

        return number_format($kb / 1024, 1).' MB';
    }

    public function render()
    {
        return view('livewire.upload-puzzle-batch');
    }
}
