<?php

namespace App\Filament\Resources\Bundles\Schemas;

use App\Models\Bundle;
use App\Models\Challenge;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bundle Details')->schema([
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
                        ->default(fn (): string => sprintf('BUND-%05d', (Bundle::query()->max('id') ?? 0) + 1))
                        ->helperText('Auto-generated on create, but can be overridden.'),
                    RichEditor::make('description')
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Pricing')->schema([
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
                    Toggle::make('is_active')
                        ->required(),
                ])->columns(2),

                Section::make('Challenges')->schema([
                    Select::make('challenges')
                        ->relationship('challenges', 'name')
                        ->options(fn (): array => Challenge::query()->active()->orderBy('name')->pluck('name', 'id')->all())
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->helperText('Select the challenges included in this bundle.'),
                ]),
            ]);
    }
}
