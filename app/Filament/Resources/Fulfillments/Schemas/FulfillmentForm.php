<?php

namespace App\Filament\Resources\Fulfillments\Schemas;

use App\Models\Enrollment;
use App\Models\Fulfillment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;

class FulfillmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fulfillment')
                    ->schema([
                        Select::make('enrollment_id')
                            ->relationship(
                                name: 'enrollment',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query->with(['user:id,name', 'challenge:id,name', 'orderItem:id,order_id']),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Enrollment $record): string => static::formatEnrollmentLabel($record))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $enrollment = filled($state)
                                    ? Enrollment::query()->with(['user'])->find($state)
                                    : null;

                                $addressSnapshot = $enrollment?->user?->addressSnapshot();

                                if ($addressSnapshot) {
                                    $set('address_snapshot', $addressSnapshot);
                                }
                            })
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'ready_to_ship' => 'Ready To Ship',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                            ])
                            ->required(),
                        TextInput::make('courier'),
                        TextInput::make('tracking_number'),
                        TextInput::make('tracking_url')
                            ->url()
                            ->columnSpanFull(),
                        DateTimePicker::make('shipped_at'),
                        DateTimePicker::make('delivered_at'),
                        KeyValue::make('address_snapshot')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Linked Records')
                    ->schema([
                        Placeholder::make('linked_enrollment')
                            ->label('Enrollment')
                            ->content(fn (?Fulfillment $record): string => static::getEnrollmentSummary($record)),
                        Placeholder::make('linked_order')
                            ->label('Order')
                            ->content(fn (?Fulfillment $record): string => static::getOrderSummary($record)),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function formatEnrollmentLabel(Enrollment $record): string
    {
        return sprintf(
            'Enrollment #%d - Order #%s - %s / %s',
            $record->id,
            $record->orderItem?->order_id ?? '-',
            $record->user?->name ?? 'Unknown player',
            $record->challenge?->name ?? 'Unknown challenge',
        );
    }

    protected static function getEnrollmentSummary(?Fulfillment $record): string
    {
        if (! $record?->enrollment) {
            return 'No linked enrollment yet.';
        }

        $record->loadMissing('enrollment.user', 'enrollment.challenge');

        return sprintf(
            'Enrollment #%d for %s / %s (%s)',
            $record->enrollment->id,
            $record->enrollment->user?->name ?? 'Unknown player',
            $record->enrollment->challenge?->name ?? 'Unknown challenge',
            ucfirst((string) $record->enrollment->status),
        );
    }

    protected static function getOrderSummary(?Fulfillment $record): string
    {
        if (! $record?->enrollment?->orderItem) {
            return 'No linked order yet.';
        }

        $record->loadMissing('enrollment.orderItem.order.user');

        $order = $record->enrollment->orderItem->order;

        if (! $order) {
            return 'No linked order yet.';
        }

        return sprintf(
            'Order #%d for %s (%s)',
            $order->id,
            $order->user?->name ?? 'Unknown player',
            ucfirst((string) $order->status),
        );
    }
}
