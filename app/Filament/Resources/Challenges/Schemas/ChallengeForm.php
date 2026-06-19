<?php

namespace App\Filament\Resources\Challenges\Schemas;

use App\Models\Challenge;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
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
                    RichEditor::make('description')
                        ->columnSpanFull(),
                    TagsInput::make('rules')
                        ->columnSpanFull()
                        ->helperText('Press enter to add a rule line.'),
                ])->columns(2),

                Section::make('Pricing & Completion')->schema([
                    TextInput::make('price_usd')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0.0),
                    TextInput::make('price_myr')
                        ->required()
                        ->numeric()
                        ->prefix('RM')
                        ->default(0.0),
                    TextInput::make('puzzle_count')
                        ->required()
                        ->numeric()
                        ->default(100),
                    Toggle::make('is_active')
                        ->required(),
                ])->columns(2),

                Section::make('Medal / Fulfillment')->schema([
                    TextInput::make('medal_weight')
                        ->label('Medal Weight (kg)')
                        ->numeric()
                        ->placeholder('0.50')
                        ->helperText('Overrides courier default weight when this challenge is fulfilled.'),
                    TextInput::make('medal_length')
                        ->label('Length (cm)')
                        ->numeric()
                        ->placeholder('10'),
                    TextInput::make('medal_width')
                        ->label('Width (cm)')
                        ->numeric()
                        ->placeholder('10'),
                    TextInput::make('medal_height')
                        ->label('Height (cm)')
                        ->numeric()
                        ->placeholder('5'),
                ])->columns(4),

                Section::make('Artwork')->schema([
                    FileUpload::make('medal_artwork')
                        ->disk('public')
                        ->image()
                        ->directory('artworks/medals'),
                    FileUpload::make('sticker_artwork')
                        ->disk('public')
                        ->image()
                        ->directory('artworks/stickers'),
                ])->columns(2),
            ]);
    }
}
