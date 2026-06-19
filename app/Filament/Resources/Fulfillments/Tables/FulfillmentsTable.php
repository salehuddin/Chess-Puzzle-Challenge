<?php

namespace App\Filament\Resources\Fulfillments\Tables;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Fulfillment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FulfillmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('enrollment.orderItem.order_id')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('enrollment.user.name')
                    ->label('Player')
                    ->searchable(),
                TextColumn::make('enrollment.challenge.name')
                    ->label('Challenge')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'ready_to_ship' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('courier')
                    ->searchable(),
                TextColumn::make('tracking_number')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('tracking_url')
                    ->url(fn ($record) => $record->tracking_url)
                    ->openUrlInNewTab()
                    ->toggleable(),
                TextColumn::make('shipped_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'ready_to_ship' => 'Ready To Ship',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('enrollment')
                    ->label('Enrollment')
                    ->icon('heroicon-o-user-group')
                    ->url(fn (Fulfillment $record): string => EnrollmentResource::getUrl('edit', ['record' => $record->enrollment])),
                Action::make('order')
                    ->label('Order')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn (Fulfillment $record): ?string => $record->enrollment?->orderItem?->order ? OrderResource::getUrl('edit', ['record' => $record->enrollment->orderItem->order]) : null)
                    ->visible(fn (Fulfillment $record): bool => (bool) $record->enrollment?->orderItem?->order),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
