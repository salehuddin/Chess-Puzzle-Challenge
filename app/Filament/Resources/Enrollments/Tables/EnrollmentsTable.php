<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Filament\Resources\Fulfillments\FulfillmentResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Enrollment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('challenge.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orderItem.order_id')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fulfillment.status')
                    ->label('Fulfillment')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? str_replace('_', ' ', ucfirst($state)) : 'Not created')
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'gray',
                        'ready_to_ship' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('activated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
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
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('order')
                    ->label('Order')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn (Enrollment $record): ?string => $record->orderItem?->order ? OrderResource::getUrl('edit', ['record' => $record->orderItem->order]) : null)
                    ->visible(fn (Enrollment $record): bool => (bool) $record->orderItem?->order),
                Action::make('fulfillment')
                    ->label('Fulfillment')
                    ->icon('heroicon-o-truck')
                    ->url(fn (Enrollment $record): ?string => $record->fulfillment ? FulfillmentResource::getUrl('edit', ['record' => $record->fulfillment]) : null)
                    ->visible(fn (Enrollment $record): bool => (bool) $record->fulfillment),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
