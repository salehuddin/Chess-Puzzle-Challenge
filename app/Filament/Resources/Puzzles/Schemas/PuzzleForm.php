<?php

namespace App\Filament\Resources\Puzzles\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PuzzleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ViewField::make('preview')
                    ->view('filament.resources.puzzles.preview')
                    ->columnSpanFull()
                    ->hidden(fn (string $operation): bool => $operation === 'create'),

                Section::make('Puzzle Data')->schema([
                    TextInput::make('lichess_id')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('fen')
                        ->required(),
                    TagsInput::make('moves')
                        ->required(),
                    TextInput::make('rating')
                        ->required()
                        ->numeric(),
                    TextInput::make('rating_deviation')
                        ->required()
                        ->numeric()
                        ->default(0),
                    TextInput::make('popularity')
                        ->required()
                        ->numeric()
                        ->default(0),
                    TextInput::make('nb_plays')
                        ->required()
                        ->numeric()
                        ->default(0),
                    TagsInput::make('themes'),
                    TextInput::make('game_url')
                        ->url(),
                ])->columns(2),
            ]);
    }
}
