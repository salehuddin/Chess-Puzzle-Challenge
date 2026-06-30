<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query): void {
                $query->select('users.*')->addSelect([
                    'paid_orders_count' => Order::selectRaw('COUNT(*)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'paid'),
                    'lifetime_spent_usd' => Order::selectRaw('COALESCE(SUM(total_amount), 0)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'paid')
                        ->where('currency', 'USD'),
                    'active_enrollments_count' => Enrollment::selectRaw('COUNT(*)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'active'),
                    'completed_challenges_count' => Enrollment::selectRaw('COUNT(*)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'completed'),
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not verified')
                    ->badge(fn ($state): bool => filled($state))
                    ->color(fn ($state): string => filled($state) ? 'success' : 'danger'),
                TextColumn::make('roles')
                    ->label('Roles')
                    ->state(fn ($record): string => $record->roles->isNotEmpty() ? $record->roles->pluck('name')->implode(', ') : '—')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('paid_orders_count')
                    ->label('Paid Orders')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('lifetime_spent_usd')
                    ->label('Spent (USD)')
                    ->money('USD')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('active_enrollments_count')
                    ->label('Active')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->color('info'),
                TextColumn::make('completed_challenges_count')
                    ->label('Completed')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->color('success'),
                TextColumn::make('country')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Email verified')
                    ->nullable()
                    ->placeholder('All users')
                    ->trueLabel('Verified only')
                    ->falseLabel('Unverified only')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                        blank: fn ($query) => $query,
                    ),
                SelectFilter::make('country')
                    ->options(fn () => User::query()->whereNotNull('country')->pluck('country', 'country')->unique()->sort()->all())
                    ->searchable(),
                TernaryFilter::make('has_paid_orders')
                    ->label('Has paid orders')
                    ->placeholder('All users')
                    ->trueLabel('With paid orders')
                    ->falseLabel('Without paid orders')
                    ->queries(
                        true: fn ($query) => $query->whereHas('orders', fn ($q) => $q->where('status', 'paid')),
                        false: fn ($query) => $query->whereDoesntHave('orders', fn ($q) => $q->where('status', 'paid')),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record): string => UserResource::getUrl('view', ['record' => $record])),
                EditAction::make(),
                Action::make('orders')
                    ->label('Orders')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn ($record): string => UserResource::getUrl('orders', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
