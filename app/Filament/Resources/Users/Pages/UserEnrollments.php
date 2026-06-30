<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Filament\Resources\Fulfillments\FulfillmentResource;
use App\Filament\Resources\Users\Pages\Concerns\HasUserRecordHeader;
use App\Filament\Resources\Users\UserResource;
use App\Models\PuzzleProgress;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserEnrollments extends ManageRelatedRecords
{
    use HasUserRecordHeader;

    protected static string $resource = UserResource::class;

    protected static string $relationship = 'enrollments';

    protected static ?string $navigationLabel = 'Enrollments';

    protected static ?string $title = 'User Enrollments';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('challenge', 'fulfillment'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('challenge.name')
                    ->label('Challenge')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function ($record): string {
                        $total = max((int) ($record->challenge?->puzzle_count ?? 0), 1);

                        $completed = PuzzleProgress::query()
                            ->where('user_id', $record->user_id)
                            ->where('challenge_id', $record->challenge_id)
                            ->whereNotNull('solved_at')
                            ->count();

                        $percent = round(($completed / $total) * 100);

                        return $completed.' / '.$total.' ('.$percent.'%)';
                    })
                    ->badge(),
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
                    })
                    ->placeholder('Not created'),
                TextColumn::make('activated_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not activated')
                    ->toggleable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not completed'),
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
                Action::make('open_enrollment')
                    ->label('Open enrollment')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => EnrollmentResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                Action::make('open_fulfillment')
                    ->label('Fulfillment')
                    ->icon('heroicon-o-truck')
                    ->url(fn ($record): ?string => $record->fulfillment ? FulfillmentResource::getUrl('edit', ['record' => $record->fulfillment]) : null)
                    ->visible(fn ($record): bool => (bool) $record->fulfillment)
                    ->openUrlInNewTab(),
            ]);
    }
}
