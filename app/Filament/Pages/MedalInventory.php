<?php

namespace App\Filament\Pages;

use App\Models\Challenge;
use App\Services\MedalInventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class MedalInventory extends Page implements HasSchemas, HasTable
{
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Medal Inventory';

    protected static ?string $title = 'Medal Inventory';

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->isFulfillment());
    }

    protected string $view = 'filament.pages.medal-inventory';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Challenge::query()
                    ->withCount([
                        'enrollments as ready_to_ship_count' => fn (Builder $query): Builder => $query
                            ->where('status', 'completed')
                            ->whereHas('fulfillment', fn (Builder $inner): Builder => $inner->where('status', 'ready_to_ship')),
                    ])
            )
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('name')
                    ->label('Challenge')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('medal_stock_on_hand')
                    ->label('On Hand')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('ready_to_ship_count')
                    ->label('Reserved')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->color(fn ($state): string => $state > 0 ? 'warning' : 'gray'),
                TextColumn::make('medal_stock_available')
                    ->label('Available')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->color(fn (Challenge $record): string => $record->medal_is_out_of_stock ? 'danger' : ($record->medal_is_low_stock ? 'warning' : 'success'))
                    ->badge(fn (Challenge $record): bool => $record->medal_is_out_of_stock || $record->medal_is_low_stock),
                TextColumn::make('medal_reorder_threshold')
                    ->label('Threshold')
                    ->numeric()
                    ->alignRight()
                    ->toggleable(),
                TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Challenge $record): string => match (true) {
                        $record->medal_is_out_of_stock => 'Out of Stock',
                        $record->medal_is_low_stock => 'Low Stock',
                        default => 'OK',
                    })
                    ->color(fn (Challenge $record): string => match (true) {
                        $record->medal_is_out_of_stock => 'danger',
                        $record->medal_is_low_stock => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereColumn('medal_stock_on_hand', '<=', 'medal_reorder_threshold')),
                Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('medal_stock_on_hand', 0)),
            ])
            ->defaultSort('medal_stock_on_hand', 'asc')
            ->recordActions([
                Action::make('restock')
                    ->label('Restock')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading(fn (Challenge $record): string => "Restock medals for {$record->name}")
                    ->modalDescription(fn (Challenge $record): string => "Current stock on hand: {$record->medal_stock_on_hand}. Enter the quantity of new medals being added to inventory.")
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Restock Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('50'),
                        Textarea::make('note')
                            ->label('Note')
                            ->rows(2)
                            ->placeholder('Supplier name, PO number, etc. (optional)'),
                    ])
                    ->action(function (Challenge $record, array $data): void {
                        app(MedalInventoryService::class)
                            ->restock($record, (int) $data['quantity'], $data['note'] ?? null, auth()->user());

                        Notification::make()
                            ->title('Stock restocked')
                            ->body("Added {$data['quantity']} medal(s) to {$record->name}.")
                            ->success()
                            ->send();
                    }),
                Action::make('adjust')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-adjustments-vertical')
                    ->color('warning')
                    ->modalHeading(fn (Challenge $record): string => "Adjust stock for {$record->name}")
                    ->modalDescription(fn (Challenge $record): string => 'Set the absolute stock count. A movement will be recorded with the difference. Use for cycle counts or corrections.')
                    ->schema([
                        TextInput::make('new_balance')
                            ->label('New Stock On Hand')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(fn (Challenge $record): int => $record->medal_stock_on_hand),
                        Textarea::make('note')
                            ->label('Reason')
                            ->rows(2)
                            ->placeholder('Cycle count, damaged units, etc.'),
                    ])
                    ->action(function (Challenge $record, array $data): void {
                        app(MedalInventoryService::class)
                            ->adjust($record, (int) $data['new_balance'], $data['note'] ?? null, auth()->user());

                        Notification::make()
                            ->title('Stock adjusted')
                            ->body("{$record->name} stock set to {$data['new_balance']}.")
                            ->success()
                            ->send();
                    }),
                Action::make('viewMovements')
                    ->label('View Movements')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->modalHeading(fn (Challenge $record): string => "Stock movements for {$record->name}")
                    ->modalSubmitActionLabel('Close')
                    ->modalCancelAction(false)
                    ->schema([
                        Select::make('movement_limit')
                            ->label('Show')
                            ->options([
                                '10' => 'Last 10 movements',
                                '25' => 'Last 25 movements',
                                '50' => 'Last 50 movements',
                            ])
                            ->default('10')
                            ->live(),
                    ])
                    ->modalContent(function (Challenge $record, array $data): Htmlable {
                        $limit = (int) ($data['movement_limit'] ?? 10);

                        $movements = $record->stockMovements()
                            ->with('user:id,name')
                            ->latest()
                            ->limit($limit)
                            ->get();

                        return view('filament.partials.medal-stock-movements', [
                            'movements' => $movements,
                            'challenge' => $record,
                        ]);
                    }),
            ]);
    }
}
