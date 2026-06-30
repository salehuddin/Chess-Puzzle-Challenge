<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Pages\Concerns\HasUserRecordHeader;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserStickers extends ManageRelatedRecords
{
    use HasUserRecordHeader;

    protected static string $resource = UserResource::class;

    protected static string $relationship = 'stickers';

    protected static ?string $navigationLabel = 'Medals & Stickers';

    protected static ?string $title = 'User Medals & Stickers';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('challenge'))
            ->defaultSort('unlocked_at', 'desc')
            ->columns([
                TextColumn::make('challenge.name')
                    ->label('Challenge')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unlocked_at')
                    ->label('Unlocked at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Locked')
                    ->badge(fn ($state): bool => filled($state))
                    ->color(fn ($state): string => filled($state) ? 'warning' : 'gray')
                    ->icon(fn ($state): string => filled($state) ? 'heroicon-o-star' : 'heroicon-o-lock-closed'),
                TextColumn::make('created_at')
                    ->label('Record created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ]);
    }
}
