<?php

namespace App\Filament\Resources\Challenges\Tables;

use App\Filament\Resources\Challenges\ChallengeResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChallengesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('puzzles'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('description')
                    ->limit(60)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('medal_artwork')
                    ->circular(),
                ImageColumn::make('sticker_artwork')
                    ->circular(),
                TextColumn::make('price_usd')
                    ->numeric()
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('price_myr')
                    ->numeric()
                    ->money('MYR')
                    ->sortable(),
                TextColumn::make('puzzles_count')
                    ->label('Puzzles count')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Details')
                        ->url(fn ($record): string => ChallengeResource::getUrl('details', ['record' => $record])),
                    Action::make('analytics')
                        ->icon('heroicon-o-chart-bar')
                        ->url(fn ($record): string => ChallengeResource::getUrl('analytics', ['record' => $record])),
                    Action::make('content')
                        ->icon('heroicon-o-document-text')
                        ->url(fn ($record): string => ChallengeResource::getUrl('content', ['record' => $record])),
                    Action::make('puzzles')
                        ->icon('heroicon-o-puzzle-piece')
                        ->url(fn ($record): string => ChallengeResource::getUrl('puzzles', ['record' => $record])),
                    Action::make('players')
                        ->icon('heroicon-o-users')
                        ->url(fn ($record): string => ChallengeResource::getUrl('players', ['record' => $record])),
                    Action::make('medalStatus')
                        ->label('Medal Status')
                        ->icon('heroicon-o-truck')
                        ->url(fn ($record): string => ChallengeResource::getUrl('medal-status', ['record' => $record])),
                ])->label('Manage'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
