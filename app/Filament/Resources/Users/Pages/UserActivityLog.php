<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Pages\Concerns\HasUserRecordHeader;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Users\Widgets\UserActivityTimeline;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserActivityLog extends ManageRelatedRecords
{
    use HasUserRecordHeader;

    protected static string $resource = UserResource::class;

    protected static string $relationship = 'activities';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?string $title = 'User Activity Log';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('causer'))
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->columns([
                TextColumn::make('created_at')
                    ->label('Occurred at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'restored' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Event')
                    ->wrap()
                    ->limit(80),
                TextColumn::make('log_name')
                    ->label('Log')
                    ->badge()
                    ->color('gray')
                    ->placeholder('default')
                    ->toggleable(),
                TextColumn::make('causer.name')
                    ->label('Performed by')
                    ->placeholder('System')
                    ->toggleable(),
                TextColumn::make('attribute_changes')
                    ->label('Changes')
                    ->state(function ($record): string {
                        $changes = $record->attribute_changes ?? collect();

                        if ($changes->isEmpty()) {
                            return '—';
                        }

                        $attributes = $changes->get('attributes', []);
                        $old = $changes->get('old', []);

                        if (! is_array($attributes)) {
                            return '—';
                        }

                        $parts = [];

                        foreach ($attributes as $key => $value) {
                            $previous = $old[$key] ?? null;
                            $parts[] = $key.': '.$this->formatChangeValue($previous).' → '.$this->formatChangeValue($value);
                        }

                        return implode('  ·  ', $parts);
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                    ]),
            ]);
    }

    protected function formatChangeValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return '∅';
        }

        return (string) $value;
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            UserActivityTimeline::class,
        ];
    }
}
