<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Filament\Resources\Fulfillments\FulfillmentResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
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
                    }),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->money(fn ($record) => strtoupper((string) ($record->currency ?: 'USD')))
                    ->sortable(),
                TextColumn::make('payment_provider')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('payment_intent_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                EditAction::make(),
                Action::make('enrollments')
                    ->label('Enrollments')
                    ->icon('heroicon-o-user-group')
                    ->url(fn (Order $record): string => EnrollmentResource::getUrl('index', ['tableSearch' => (string) $record->id])),
                Action::make('fulfillments')
                    ->label('Fulfillments')
                    ->icon('heroicon-o-truck')
                    ->url(fn (Order $record): string => FulfillmentResource::getUrl('index', ['tableSearch' => (string) $record->id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
