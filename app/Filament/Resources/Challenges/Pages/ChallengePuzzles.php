<?php

namespace App\Filament\Resources\Challenges\Pages;

use App\Filament\Resources\Challenges\ChallengeResource;
use App\Filament\Resources\Challenges\Pages\Concerns\HasChallengeRecordHeader;
use App\Models\Puzzle;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class ChallengePuzzles extends ManageRelatedRecords
{
    use HasChallengeRecordHeader;

    protected static string $resource = ChallengeResource::class;

    protected static string $relationship = 'puzzles';

    protected static ?string $navigationLabel = 'Puzzles';

    protected static ?string $title = 'Challenge Puzzles';

    #[Url(as: 'preview')]
    public ?int $previewPuzzleId = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        EmbeddedTable::make()
                            ->columnSpan(7),
                        SchemaView::make('filament.resources.puzzles.partials.side-preview-panel')
                            ->columnSpan(5),
                    ]),
            ]);
    }

    public function getPreviewPuzzle(): ?Puzzle
    {
        if (! $this->previewPuzzleId) {
            return null;
        }

        return $this->getOwnerRecord()
            ->puzzles()
            ->select(['puzzles.id', 'puzzles.lichess_id', 'puzzles.fen', 'puzzles.moves', 'puzzles.rating', 'puzzles.themes', 'puzzles.popularity', 'puzzles.nb_plays'])
            ->where('puzzles.id', $this->previewPuzzleId)
            ->first();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('lichess_id')
            ->reorderable('pivot.sequence')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->select([
                'puzzles.id',
                'puzzles.lichess_id',
                'puzzles.rating',
                'puzzles.themes',
                'puzzles.fen',
                'puzzles.moves',
            ]))
            ->columns([
                TextColumn::make('lichess_id')
                    ->label('Lichess ID')
                    ->description(fn (Puzzle $record): ?string => $record->fen)
                    ->extraAttributes(fn (Puzzle $record): array => [
                        'data-preview-id' => (string) $record->id,
                        'data-preview-lichess-id' => (string) $record->lichess_id,
                        'data-preview-fen' => (string) $record->fen,
                        'data-preview-moves' => base64_encode(is_array($record->moves) ? json_encode($record->moves) : (string) $record->moves),
                        'data-preview-rating' => (string) ($record->rating ?? ''),
                        'data-preview-themes' => base64_encode(is_array($record->themes) ? json_encode($record->themes) : (string) ($record->themes ?? '[]')),
                    ])
                    ->searchable(),
                TextColumn::make('pivot.sequence')
                    ->label('Sequence')
                    ->numeric(),
                TextColumn::make('rating')
                    ->sortable(),
                TextColumn::make('themes')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(3),
            ])
            ->recordClasses(fn (Puzzle $record): array => $this->previewPuzzleId === $record->id
                ? [
                    'bg-amber-50/80',
                    'ring-1',
                    'ring-inset',
                    'ring-amber-300',
                ]
                : [])
            ->headerActions([
                Action::make('attachPuzzles')
                    ->label('Attach Puzzles')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => ChallengeResource::getUrl('attach-puzzles', ['record' => $this->getRecord()])),
                Action::make('randomize')
                    ->label('Randomize Sequence')
                    ->icon('heroicon-o-arrows-right-left')
                    ->requiresConfirmation()
                    ->action(fn () => $this->randomizeSequence()),
                Action::make('sortRatingAsc')
                    ->label('Sort Rating ASC')
                    ->icon('heroicon-o-bars-arrow-up')
                    ->action(fn () => $this->sortSequenceByRating('asc')),
                Action::make('sortRatingDesc')
                    ->label('Sort Rating DESC')
                    ->icon('heroicon-o-bars-arrow-down')
                    ->action(fn () => $this->sortSequenceByRating('desc')),
            ])
            ->actions([
                DetachAction::make()
                    ->after(function (): void {
                        $this->normalizeSequence();
                        $this->resetTable();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->fetchSelectedRecords(false)
                        ->after(function (): void {
                            $this->normalizeSequence();
                            $this->resetTable();
                        }),
                ]),
            ]);
    }

    protected function randomizeSequence(): void
    {
        $pairs = $this->getOwnerRecord()
            ->puzzles()
            ->select(['puzzles.id'])
            ->pluck('puzzles.id')
            ->shuffle()
            ->values();

        $this->persistSequenceOrder($pairs->all());
    }

    protected function sortSequenceByRating(string $direction): void
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $pairs = $this->getOwnerRecord()
            ->puzzles()
            ->select(['puzzles.id', 'puzzles.rating'])
            ->orderBy('puzzles.rating', $direction)
            ->pluck('puzzles.id')
            ->values();

        $this->persistSequenceOrder($pairs->all());
    }

    /**
     * @param  array<int, int>  $puzzleIds
     */
    protected function persistSequenceOrder(array $puzzleIds): void
    {
        if ($puzzleIds === []) {
            return;
        }

        DB::transaction(function () use ($puzzleIds): void {
            foreach ($puzzleIds as $index => $puzzleId) {
                DB::table('challenge_puzzle')
                    ->where('challenge_id', $this->getOwnerRecord()->id)
                    ->where('puzzle_id', $puzzleId)
                    ->update(['sequence' => $index + 1]);
            }
        });

        $this->resetTable();
    }

    protected function normalizeSequence(): void
    {
        $orderedPuzzleIds = $this->getOwnerRecord()
            ->puzzles()
            ->select(['puzzles.id'])
            ->orderBy('challenge_puzzle.sequence')
            ->orderBy('puzzles.id')
            ->pluck('puzzles.id')
            ->all();

        if ($orderedPuzzleIds === []) {
            return;
        }

        $this->persistSequenceOrder($orderedPuzzleIds);
    }
}
