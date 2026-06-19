<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use App\Models\Enrollment;
use App\Models\OrderItem;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enrollment')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('challenge_id')
                            ->relationship('challenge', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('order_item_id')
                            ->relationship(
                                name: 'orderItem',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query->with('order.user:id,name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (OrderItem $record): string => static::formatOrderItemLabel($record))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $orderItem = filled($state)
                                    ? OrderItem::query()->with('order')->find($state)
                                    : null;

                                if (! $orderItem) {
                                    return;
                                }

                                $set('user_id', $orderItem->order?->user_id);

                                if ($orderItem->item_type === 'challenge') {
                                    $set('challenge_id', $orderItem->item_id);
                                }
                            }),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                        DateTimePicker::make('activated_at'),
                        DateTimePicker::make('completed_at'),
                    ])
                    ->columns(2),
                Section::make('Linked Records')
                    ->schema([
                        Placeholder::make('linked_order')
                            ->label('Order')
                            ->content(fn (?Enrollment $record): string => static::getOrderSummary($record)),
                        Placeholder::make('linked_fulfillment')
                            ->label('Fulfillment')
                            ->content(fn (?Enrollment $record): string => static::getFulfillmentSummary($record)),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function formatOrderItemLabel(OrderItem $record): string
    {
        return sprintf(
            'Order #%d - %s%s',
            $record->order_id,
            $record->name_snapshot ?: ('Item #' . $record->id),
            $record->order?->user?->name ? ' (' . $record->order->user->name . ')' : '',
        );
    }

    protected static function getOrderSummary(?Enrollment $record): string
    {
        if (! $record?->orderItem) {
            return 'No linked order item yet.';
        }

        $record->loadMissing('orderItem.order.user');

        $order = $record->orderItem->order;

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

    protected static function getFulfillmentSummary(?Enrollment $record): string
    {
        if (! $record?->fulfillment) {
            return 'No linked fulfillment yet.';
        }

        return sprintf(
            'Fulfillment #%d (%s)',
            $record->fulfillment->id,
            str_replace('_', ' ', ucfirst((string) $record->fulfillment->status)),
        );
    }
}
