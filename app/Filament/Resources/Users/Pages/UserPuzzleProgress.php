<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Pages\Concerns\HasUserRecordHeader;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserPuzzleProgress extends ManageRelatedRecords
{
    use HasUserRecordHeader;

    protected static string $resource = UserResource::class;

    protected static string $relationship = 'puzzleProgress';

    protected static ?string $navigationLabel = 'Puzzle Progress';

    protected static ?string $title = 'User Puzzle Progress';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('challenge', 'puzzle'))
            ->defaultSort('solved_at', 'desc')
            ->columns([
                TextColumn::make('challenge.name')
                    ->label('Challenge')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('puzzle.id')
                    ->label('Puzzle #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('solved_at')
                    ->label('Solved at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not solved yet')
                    ->badge(fn ($state): bool => filled($state))
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray')
                    ->icon(fn ($state): string => filled($state) ? 'heroicon-o-check-circle' : 'heroicon-o-clock'),
                TextColumn::make('created_at')
                    ->label('First seen at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ]);
    }
}
