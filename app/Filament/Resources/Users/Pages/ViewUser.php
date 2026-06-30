<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Pages\Concerns\HasUserRecordHeader;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Users\Widgets\UserActivityTimeline;
use App\Filament\Resources\Users\Widgets\UserOverview;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewUser extends ViewRecord
{
    use HasUserRecordHeader;

    protected static string $resource = UserResource::class;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'User Overview';

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->schema([
                    TextEntry::make('name')
                        ->label('Name'),
                    TextEntry::make('email')
                        ->label('Email address')
                        ->copyable()
                        ->url(fn ($record): string => 'mailto:'.$record->email),
                    TextEntry::make('email_verified_at')
                        ->label('Email verified at')
                        ->dateTime()
                        ->placeholder('Not verified')
                        ->badge(fn ($state): bool => filled($state))
                        ->color(fn ($state): string => filled($state) ? 'success' : 'danger')
                        ->icon(fn ($state): string => filled($state) ? 'heroicon-o-check-badge' : 'heroicon-o-x-mark'),
                    TextEntry::make('created_at')
                        ->label('Registered at')
                        ->dateTime(),
                    TextEntry::make('roles')
                        ->label('Roles')
                        ->state(fn ($record): string => $record->roles->isNotEmpty() ? $record->roles->pluck('name')->implode(', ') : 'None')
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('updated_at')
                        ->label('Last updated at')
                        ->dateTime()
                        ->placeholder('Never'),
                ])
                ->columns(2)
                ->icon('heroicon-o-user-circle'),
            Section::make('Address')
                ->schema([
                    TextEntry::make('address_line1')
                        ->label('Address line 1')
                        ->placeholder('Not provided'),
                    TextEntry::make('address_line2')
                        ->label('Address line 2')
                        ->placeholder('Not provided'),
                    TextEntry::make('city')
                        ->label('City')
                        ->placeholder('Not provided'),
                    TextEntry::make('state')
                        ->label('State')
                        ->placeholder('Not provided'),
                    TextEntry::make('postcode')
                        ->label('Postcode')
                        ->placeholder('Not provided'),
                    TextEntry::make('country')
                        ->label('Country')
                        ->placeholder('Not provided'),
                ])
                ->columns(2)
                ->icon('heroicon-o-map-pin'),
        ]);
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            UserOverview::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            UserActivityTimeline::class,
        ];
    }
}
