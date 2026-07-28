<?php

namespace App\Filament\Resources\Challenges\Schemas;

use App\Models\Challenge;
use App\Support\NavigationMode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ChallengeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Challenge Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn (): string => sprintf('CHAL-%05d', (Challenge::query()->max('id') ?? 0) + 1))
                        ->helperText('Auto-generated on create, but can be overridden.'),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Player Progression Policy')
                    ->description('How players navigate this challenge. Defaults to strict sequential — the original Lichess-style flow.')
                    ->schema([
                        Select::make('puzzle_navigation_mode')
                            ->label('Navigation mode')
                            ->options(collect(NavigationMode::cases())->mapWithKeys(fn (NavigationMode $m) => [$m->value => $m->label()]))
                            ->default(NavigationMode::Strict->value)
                            ->native(false)
                            ->live()
                            ->helperText(fn (NavigationMode $state) => $state?->description())
                            ->columnSpanFull(),
                        TextInput::make('skip_token_count')
                            ->label('Skip tokens')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->nullable()
                            ->placeholder('3 (default)')
                            ->visible(fn (?NavigationMode $state) => $state?->allowsSkip())
                            ->helperText('Number of puzzles the player can skip and revisit later. Leave empty for the 3-token default.'),
                        TextInput::make('autosolve_threshold')
                            ->label('Reveal-solution threshold')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->nullable()
                            ->placeholder('5 (default)')
                            ->visible(fn (?NavigationMode $state) => $state?->allowsAutoSolve())
                            ->helperText('After this many failed attempts, the player may reveal the solution. The puzzle counts as solved-with-help. Leave empty for the 5-attempt default.'),
                    ])->columns(2),
            ]);
    }
}
