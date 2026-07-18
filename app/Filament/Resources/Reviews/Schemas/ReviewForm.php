<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Review;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review Content')
                    ->schema([
                        Placeholder::make('player')
                            ->content(fn (?Review $record): string => $record?->user?->name ?? 'Unknown player'),
                        Placeholder::make('challenge')
                            ->content(fn (?Review $record): string => $record?->challenge?->name ?? 'Unknown challenge'),
                        Placeholder::make('submitted_at')
                            ->content(fn (?Review $record): ?string => $record?->submitted_at?->toDateTimeString()),
                        Placeholder::make('puzzle_rating')
                            ->content(fn (?Review $record): string => static::formatRating($record?->puzzle_rating)),
                        Placeholder::make('platform_rating')
                            ->content(fn (?Review $record): string => static::formatRating($record?->platform_rating)),
                    ])
                    ->columns(2),
                Section::make('Feedback')
                    ->schema([
                        TextInput::make('title')
                            ->label('Headline')
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->label('Player feedback')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
                Section::make('Moderation')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'submitted' => 'Submitted',
                            ])
                            ->required(),
                        Toggle::make('is_public')
                            ->label('Show on testimonials section')
                            ->helperText('When on, this review may appear in the public landing page testimonials.'),
                        Toggle::make('is_featured')
                            ->label('Feature prominently')
                            ->helperText('Featured reviews are surfaced first on the landing page. Implies public visibility.'),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function formatRating(?int $value): string
    {
        if (! $value) {
            return 'Not rated';
        }

        $pieces = ['Pawn', 'Knight', 'Bishop', 'Rook', 'Queen'];

        return ($pieces[$value - 1] ?? 'Unknown').' ('.$value.'/5)';
    }
}
