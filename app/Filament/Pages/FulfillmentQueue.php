<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Fulfillments\FulfillmentResource;
use App\Jobs\ProcessCourierShipmentJob;
use App\Models\Fulfillment;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FulfillmentQueue extends Page implements HasSchemas, HasTable
{
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Fulfillment Queue';

    protected static ?string $title = 'Fulfillment Queue';

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->isEditor() || $user->isFulfillment());
    }

    protected string $view = 'filament.pages.fulfillment-queue';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Fulfillment::query()
                    ->with(['enrollment.user:id,name,email', 'enrollment.challenge:id,name,sku,medal_stock_on_hand', 'enrollment.orderItem:id,order_id'])
                    ->whereIn('status', ['ready_to_ship', 'shipped'])
            )
            ->columns([
                TextColumn::make('enrollment.orderItem.order_id')
                    ->label('Order #')
                    ->sortable(),
                TextColumn::make('enrollment.user.name')
                    ->label('Player')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('enrollment.user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('enrollment.challenge.name')
                    ->label('Challenge')
                    ->searchable()
                    ->description(fn (Fulfillment $record): ?string => $record->enrollment?->challenge?->medal_stock_on_hand <= 0
                        ? '⚠ No medal stock available'
                        : null),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ready_to_ship' => 'warning',
                        'shipped' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('courier')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('tracking_number')
                    ->label('Tracking #')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('tracking_url')
                    ->label('Tracking URL')
                    ->url(fn (Fulfillment $record): ?string => $record->tracking_url)
                    ->openUrlInNewTab()
                    ->toggleable(),
                TextColumn::make('enrollment.challenge.medal_stock_on_hand')
                    ->label('Stock')
                    ->alignRight()
                    ->badge(fn (Fulfillment $record): bool => $record->enrollment?->challenge?->medal_stock_on_hand <= 0)
                    ->color(fn (Fulfillment $record): string => $record->enrollment?->challenge?->medal_stock_on_hand <= 0 ? 'danger' : 'gray')
                    ->formatStateUsing(fn (Fulfillment $record): string => $record->enrollment?->challenge?->medal_stock_on_hand <= 0 ? '0' : (string) $record->enrollment?->challenge?->medal_stock_on_hand)
                    ->toggleable(),
                TextColumn::make('enrollment.completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('shipped_at')
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
                        'ready_to_ship' => 'Ready To Ship',
                        'shipped' => 'Shipped',
                    ]),
                Filter::make('completed_not_shipped')
                    ->label('Completed Not Shipped')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'ready_to_ship')),
                Filter::make('shipped_missing_tracking')
                    ->label('Shipped Missing Tracking')
                    ->query(function (Builder $query): Builder {
                        return $query
                            ->where('status', 'shipped')
                            ->where(function (Builder $trackingQuery): void {
                                $trackingQuery
                                    ->where(function (Builder $inner): void {
                                        $inner
                                            ->whereNull('tracking_number')
                                            ->orWhere('tracking_number', '');
                                    })
                                    ->where(function (Builder $inner): void {
                                        $inner
                                            ->whereNull('tracking_url')
                                            ->orWhere('tracking_url', '');
                                    });
                            });
                    }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                Action::make('dispatchShipment')
                    ->label('Dispatch Shipment')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Dispatch shipment?')
                    ->modalDescription('This will queue a courier job for this fulfillment.')
                    ->modalSubmitActionLabel('Yes, dispatch')
                    ->action(function (Fulfillment $record): void {
                        ProcessCourierShipmentJob::dispatch($record->id);

                        Notification::make()
                            ->title('Shipment queued')
                            ->body("Courier job dispatched for fulfillment #{$record->id}.")
                            ->success()
                            ->send();
                    }),
                Action::make('editFulfillment')
                    ->label('Open Fulfillment')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Fulfillment $record): string => FulfillmentResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('dispatchSelected')
                        ->label('Dispatch Selected')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Dispatch selected shipments?')
                        ->modalDescription('This will queue courier jobs for all selected fulfillments.')
                        ->action(function (Builder $query): void {
                            $count = 0;

                            $query->chunkById(100, function (iterable $records) use (&$count): void {
                                foreach ($records as $record) {
                                    ProcessCourierShipmentJob::dispatch($record->id);
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title('Shipments queued')
                                ->body("{$count} fulfillment(s) queued for courier dispatch.")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
