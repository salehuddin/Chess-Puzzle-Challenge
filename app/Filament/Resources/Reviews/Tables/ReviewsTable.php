<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('challenge.name')
                    ->label('Challenge')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Player')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('puzzle_rating')
                    ->label('Puzzle')
                    ->tooltip(fn (?Review $record): ?string => static::ratingTooltip($record?->puzzle_rating))
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        null => 'gray',
                        default => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'),
                    })
                    ->formatStateUsing(fn (?int $state): string => $state ? $state.'/5' : '-'),
                TextColumn::make('platform_rating')
                    ->label('Platform')
                    ->tooltip(fn (?Review $record): ?string => static::ratingTooltip($record?->platform_rating))
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        null => 'gray',
                        default => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'),
                    })
                    ->formatStateUsing(fn (?int $state): string => $state ? $state.'/5' : '-'),
                TextColumn::make('title')
                    ->label('Headline')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('body')
                    ->label('Feedback')
                    ->limit(80)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'submitted' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                    ]),
                TernaryFilter::make('is_public')
                    ->label('Public'),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
                SelectFilter::make('challenge')
                    ->relationship('challenge', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('enrollment')
                    ->label('Enrollment')
                    ->icon('heroicon-o-user-group')
                    ->url(fn (Review $record): string => EnrollmentResource::getUrl('edit', ['record' => $record->enrollment]))
                    ->visible(fn (Review $record): bool => (bool) $record->enrollment),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function ratingTooltip(?int $value): ?string
    {
        if (! $value) {
            return null;
        }

        $pieces = ['Pawn', 'Knight', 'Bishop', 'Rook', 'Queen'];

        return ($pieces[$value - 1] ?? 'Unknown').' — '.$value.' of 5';
    }
}
