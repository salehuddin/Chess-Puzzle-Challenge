<?php

namespace App\Filament\Resources\Challenges\Pages;

use App\Filament\Resources\Challenges\ChallengeResource;
use App\Filament\Resources\Challenges\Pages\Concerns\HasChallengeRecordHeader;
use App\Filament\Resources\Puzzles\Support\PuzzleThemes;
use App\Models\Puzzle;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class AttachPuzzles extends Page implements HasSchemas, HasTable
{
    use HasChallengeRecordHeader;
    use InteractsWithRecord {
        HasChallengeRecordHeader::getBreadcrumbs insteadof InteractsWithRecord;
    }
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string $resource = ChallengeResource::class;

    protected static ?string $navigationLabel = 'Attach Puzzles';

    protected static ?string $title = 'Attach Puzzles';

    #[Url(as: 'preview')]
    public ?int $previewPuzzleId = null;

    /**
     * @var array<string, mixed> | null
     */
    #[Url(as: 'filters')]
    public ?array $tableFilters = null;

    #[Url(as: 'grouping')]
    public ?string $tableGrouping = null;

    /**
     * @var ?string
     */
    #[Url(as: 'search')]
    public $tableSearch = '';

    #[Url(as: 'sort')]
    public ?string $tableSort = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

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

    public function table(Table $table): Table
    {
        return $table
            ->query(Puzzle::query())
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->select([
                    'id',
                    'lichess_id',
                    'rating',
                    'themes',
                    'popularity',
                    'nb_plays',
                    'fen',
                    'moves',
                ])
                ->whereNotIn('id', $this->getRecord()->puzzles()->pluck('puzzles.id'))
            )
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
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('themes')
                    ->badge()
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('popularity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nb_plays')
                    ->numeric()
                    ->sortable(),
            ])
            ->recordClasses(fn (Puzzle $record): array => ((int) ($this->previewPuzzleId ?? 0)) === $record->id
                ? [
                    'bg-amber-50/80',
                    'ring-1',
                    'ring-inset',
                    'ring-amber-300',
                ]
                : [])
            ->filters([
                Filter::make('rating')
                    ->label('Rating Range')
                    ->schema([
                        TextInput::make('rating_from')
                            ->label('Min Rating')
                            ->numeric()
                            ->placeholder('e.g. 1000'),
                        TextInput::make('rating_to')
                            ->label('Max Rating')
                            ->numeric()
                            ->placeholder('e.g. 2500'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['rating_from'] ?? null, fn ($q, $v) => $q->where('rating', '>=', $v))
                        ->when($data['rating_to'] ?? null, fn ($q, $v) => $q->where('rating', '<=', $v))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['rating_from'] ?? null) {
                            $indicators[] = 'Rating ≥ '.$data['rating_from'];
                        }

                        if ($data['rating_to'] ?? null) {
                            $indicators[] = 'Rating ≤ '.$data['rating_to'];
                        }

                        return $indicators;
                    }),

                SelectFilter::make('theme')
                    ->label('Theme')
                    ->multiple()
                    ->searchable()
                    ->options(PuzzleThemes::availableOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $themes = array_filter(
                            Arr::wrap($data['values'] ?? $data['value'] ?? []),
                            fn ($value): bool => filled($value),
                        );

                        if ($themes === []) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($themes): void {
                            foreach ($themes as $theme) {
                                $query->orWhereJsonContains('themes', (string) $theme);
                            }
                        });
                    }),

                Filter::make('nb_plays')
                    ->label('Min Plays')
                    ->schema([
                        TextInput::make('min_plays')
                            ->label('Minimum plays')
                            ->numeric()
                            ->placeholder('e.g. 1000'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['min_plays'] ?? null, fn ($q, $v) => $q->where('nb_plays', '>=', $v))
                    )
                    ->indicateUsing(fn (array $data): array => ($data['min_plays'] ?? null) ? ['Min plays: '.$data['min_plays']] : []
                    ),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('attach')
                        ->label('Attach selected puzzles')
                        ->icon('heroicon-o-plus')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Attach selected puzzles')
                        ->modalDescription('Add the selected puzzles to this challenge.')
                        ->modalSubmitActionLabel('Attach')
                        ->action(function (Builder $query): void {
                            $puzzleIds = $query->pluck('id')->all();

                            if ($puzzleIds === []) {
                                return;
                            }

                            $challenge = $this->getRecord();
                            $existingCount = $challenge->puzzles()->count();

                            $attachments = [];
                            foreach ($puzzleIds as $index => $puzzleId) {
                                $attachments[$puzzleId] = ['sequence' => $existingCount + $index + 1];
                            }

                            $challenge->puzzles()->attach($attachments);

                            $this->normalizeSequence();

                            Notification::make()
                                ->title(count($puzzleIds).' puzzle(s) attached')
                                ->success()
                                ->send();

                            $this->redirect(ChallengeResource::getUrl('puzzles', ['record' => $challenge]));
                        }),
                ]),
            ]);
    }

    protected function normalizeSequence(): void
    {
        $orderedPuzzleIds = $this->getRecord()
            ->puzzles()
            ->select(['puzzles.id'])
            ->orderBy('challenge_puzzle.sequence')
            ->orderBy('puzzles.id')
            ->pluck('puzzles.id')
            ->all();

        if ($orderedPuzzleIds === []) {
            return;
        }

        DB::transaction(function () use ($orderedPuzzleIds): void {
            foreach ($orderedPuzzleIds as $index => $puzzleId) {
                DB::table('challenge_puzzle')
                    ->where('challenge_id', $this->getRecord()->id)
                    ->where('puzzle_id', $puzzleId)
                    ->update(['sequence' => $index + 1]);
            }
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to challenge puzzles')
                ->icon('heroicon-o-arrow-left')
                ->url(fn (): string => ChallengeResource::getUrl('puzzles', ['record' => $this->getRecord()])),
        ];
    }
}
