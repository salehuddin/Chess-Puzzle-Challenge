<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        DateTimePicker::make('email_verified_at'),
                    ])->columns(2),

                Section::make('Roles')
                    ->schema([
                        Select::make('roles')
                            ->label('Assigned roles')
                            ->hint('Only super admins can assign staff roles.')
                            ->relationship('roles', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->whereIn('name', ['super_admin', 'editor', 'fulfillment']))
                            ->multiple()
                            ->preload()
                            ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
                            ->columnSpanFull(),
                    ]),

                Section::make('Address')
                    ->schema([
                        TextInput::make('address_line1'),
                        TextInput::make('address_line2'),
                        TextInput::make('city'),
                        TextInput::make('state'),
                        TextInput::make('postcode'),
                        TextInput::make('country')
                            ->default('MY'),
                    ])->columns(2),
            ]);
    }
}
