<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Bundle;
use App\Models\Challenge;
use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Items')
                    ->visible(fn (?Order $record): bool => blank($record))
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Select::make('item_type')
                                    ->label('Type')
                                    ->options([
                                        'challenge' => 'Challenge',
                                        'bundle' => 'Bundle',
                                    ])
                                    ->default('challenge')
                                    ->live(),
                                Select::make('item_id')
                                    ->label(function (Get $get): string {
                                        return $get('item_type') === 'bundle' ? 'Bundle' : 'Challenge';
                                    })
                                    ->options(function (Get $get): array {
                                        if ($get('item_type') === 'bundle') {
                                            return Bundle::query()->active()->orderBy('name')->pluck('name', 'id')->all();
                                        }

                                        return Challenge::query()->active()->orderBy('name')->pluck('name', 'id')->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                                        if (blank($state)) {
                                            $set('item_price', 0);

                                            return;
                                        }

                                        $type = $get('item_type');
                                        $model = $type === 'bundle'
                                            ? Bundle::query()->find($state)
                                            : Challenge::query()->find($state);

                                        $set('item_price', $model ? (float) ($model->price_usd ?? 0) : 0);
                                    }),
                                TextInput::make('item_price')
                                    ->label('Price')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add another item')
                            ->required()
                            ->minItems(1),
                    ]),

                Section::make('Order')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(3),
                        TextInput::make('subtotal_amount')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->visible(fn (?Order $record): bool => filled($record)),
                        TextInput::make('discount_amount')
                            ->numeric()
                            ->required()
                            ->default(0),
                        TextInput::make('total_amount')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->visible(fn (?Order $record): bool => filled($record)),
                        TextInput::make('payment_provider'),
                        TextInput::make('payment_intent_id')
                            ->columnSpanFull(),
                        DateTimePicker::make('paid_at'),
                        KeyValue::make('metadata')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Linked Records')
                    ->visible(fn (?Order $record): bool => filled($record))
                    ->schema([
                        Placeholder::make('linked_enrollments')
                            ->label('Enrollments')
                            ->content(fn (?Order $record): string => static::getEnrollmentSummary($record)),
                        Placeholder::make('linked_fulfillments')
                            ->label('Fulfillments')
                            ->content(fn (?Order $record): string => static::getFulfillmentSummary($record)),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function getEnrollmentSummary(?Order $record): string
    {
        if (! $record) {
            return 'Save the order to view linked enrollments.';
        }

        $record->loadMissing('items.enrollments.challenge:id,name');

        $enrollments = $record->items->flatMap->enrollments;

        if ($enrollments->isEmpty()) {
            return 'No linked enrollments yet.';
        }

        return $enrollments
            ->map(fn ($enrollment): string => sprintf(
                '#%d %s (%s)',
                $enrollment->id,
                $enrollment->challenge?->name ?? 'Unknown challenge',
                ucfirst((string) $enrollment->status),
            ))
            ->implode(', ');
    }

    protected static function getFulfillmentSummary(?Order $record): string
    {
        if (! $record) {
            return 'Save the order to view linked fulfillments.';
        }

        $record->loadMissing('items.enrollments.fulfillment');

        $fulfillments = $record->items
            ->flatMap->enrollments
            ->map->fulfillment
            ->filter();

        if ($fulfillments->isEmpty()) {
            return 'No linked fulfillments yet.';
        }

        return $fulfillments
            ->map(fn ($fulfillment): string => sprintf('#%d %s', $fulfillment->id, str_replace('_', ' ', ucfirst((string) $fulfillment->status))))
            ->implode(', ');
    }
}
