<?php

namespace App\Filament\Resources\Puzzles\Pages;

use App\Filament\Resources\Puzzles\PuzzleResource;
use App\Models\Puzzle;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class ListPuzzles extends ListRecords
{
    protected static string $resource = PuzzleResource::class;

    #[Url(as: 'preview')]
    public ?int $previewPuzzleId = null;

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        EmbeddedTable::make()
                            ->columnSpan(7),
                        View::make('filament.resources.puzzles.partials.side-preview-panel')
                            ->columnSpan(5),
                    ]),
            ]);
    }

    public function getPreviewPuzzle(): ?Puzzle
    {
        if (! $this->previewPuzzleId) {
            return null;
        }

        return Puzzle::query()
            ->select(['id', 'lichess_id', 'fen', 'moves', 'rating', 'themes', 'popularity', 'nb_plays'])
            ->find($this->previewPuzzleId);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_csv')
                ->label('Import CSV Data')
                ->color('gray')
                ->icon('heroicon-o-cloud-arrow-up')
                ->url(fn (): string => PuzzleResource::getUrl('import')),
            Action::make('upload_batch')
                ->label('Upload CSV Batch')
                ->color('gray')
                ->icon('heroicon-o-document-arrow-up')
                ->url(fn (): string => PuzzleResource::getUrl('upload')),
            CreateAction::make(),
        ];
    }
}
