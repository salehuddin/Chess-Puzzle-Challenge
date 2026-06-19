<?php

namespace App\Filament\Resources\Puzzles\Tables;

use App\Filament\Resources\Puzzles\Support\PuzzleThemes;
use App\Models\Challenge;
use App\Models\Puzzle;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PuzzlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->select([
                'id',
                'lichess_id',
                'rating',
                'themes',
                'popularity',
                'nb_plays',
                'fen',
                'moves',
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
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordClasses(fn (Puzzle $record, $livewire): array => ((int) ($livewire->previewPuzzleId ?? 0)) === $record->id
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('attachToChallenge')
                        ->label('Attach to challenge')
                        ->icon('heroicon-o-plus-circle')
                        ->color('primary')
                        ->form([
                            Select::make('challenge_id')
                                ->label('Challenge')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->options(fn (): array => Challenge::query()->pluck('name', 'id')->all()),
                        ])
                        ->modalHeading('Attach to challenge')
                        ->modalDescription('Attach the selected puzzles to the chosen challenge.')
                        ->modalSubmitActionLabel('Attach')
                        ->action(function (array $data, Builder $query): void {
                            $challenge = Challenge::query()->find($data['challenge_id']);

                            if (! $challenge) {
                                Notification::make()
                                    ->title('Challenge not found')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $existingIds = $challenge->puzzles()->pluck('puzzles.id')->all();
                            $selectedIds = $query->pluck('id')->all();
                            $newIds = array_diff($selectedIds, $existingIds);

                            if ($newIds === []) {
                                Notification::make()
                                    ->title('All selected puzzles are already attached to this challenge.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $existingCount = $challenge->puzzles()->count();
                            $attachments = [];
                            foreach ($newIds as $index => $puzzleId) {
                                $attachments[$puzzleId] = ['sequence' => $existingCount + $index + 1];
                            }

                            $challenge->puzzles()->attach($attachments);

                            static::normalizeChallengeSequence($challenge);

                            Notification::make()
                                ->title(count($newIds).' puzzle(s) attached to '.$challenge->name)
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /**
     * Re-number the sequence values for every puzzle attached to a challenge.
     */
    protected static function normalizeChallengeSequence(Challenge $challenge): void
    {
        $orderedPuzzleIds = $challenge->puzzles()
            ->select(['puzzles.id'])
            ->orderBy('challenge_puzzle.sequence')
            ->orderBy('puzzles.id')
            ->pluck('puzzles.id')
            ->all();

        if ($orderedPuzzleIds === []) {
            return;
        }

        DB::transaction(function () use ($challenge, $orderedPuzzleIds): void {
            foreach ($orderedPuzzleIds as $index => $puzzleId) {
                DB::table('challenge_puzzle')
                    ->where('challenge_id', $challenge->id)
                    ->where('puzzle_id', $puzzleId)
                    ->update(['sequence' => $index + 1]);
            }
        });
    }
}
