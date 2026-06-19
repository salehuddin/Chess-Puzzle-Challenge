<?php

namespace App\Filament\Resources\Challenges\Pages;

use App\Filament\Resources\Challenges\ChallengeResource;
use App\Filament\Resources\Challenges\Pages\Concerns\HasChallengeRecordHeader;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EditChallenge extends EditRecord
{
    use HasChallengeRecordHeader;

    protected static string $resource = ChallengeResource::class;

    protected static ?string $navigationLabel = 'Details';

    protected static ?string $title = 'Challenge Details';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Core Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->required(),
                        TextInput::make('puzzle_count')
                            ->required()
                            ->numeric(),
                        TextInput::make('price_usd')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('price_myr')
                            ->required()
                            ->numeric()
                            ->prefix('RM'),
                        TextInput::make('meta_title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('meta_description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Visual Assets')
                    ->schema([
                        FileUpload::make('poster_image')
                            ->disk('public')
                            ->image()
                            ->directory('artworks/challenges/posters'),
                        FileUpload::make('medal_artwork')
                            ->disk('public')
                            ->image()
                            ->directory('artworks/medals'),
                        FileUpload::make('medal_images')
                            ->disk('public')
                            ->image()
                            ->multiple()
                            ->directory('artworks/medals/gallery')
                            ->reorderable(),
                        FileUpload::make('sticker_artwork')
                            ->disk('public')
                            ->image()
                            ->directory('artworks/stickers'),
                    ])
                    ->columns(2),
                Section::make('Medal / Fulfillment')
                    ->schema([
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
                    ])
                    ->columns(4),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
