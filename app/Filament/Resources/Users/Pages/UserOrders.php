<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Users\Pages\Concerns\HasUserRecordHeader;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserOrders extends ManageRelatedRecords
{
    use HasUserRecordHeader;

    protected static string $resource = UserResource::class;

    protected static string $relationship = 'orders';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?string $title = 'User Orders';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money(fn ($record) => strtoupper((string) ($record->currency ?: 'USD')))
                    ->sortable(),
                TextColumn::make('payment_provider')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not paid'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActions([
                Action::make('view_order')
                    ->label('Open order')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => OrderResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
