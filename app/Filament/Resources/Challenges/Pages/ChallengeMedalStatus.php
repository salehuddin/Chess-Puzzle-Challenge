<?php

namespace App\Filament\Resources\Challenges\Pages;

use App\Filament\Resources\Challenges\ChallengeResource;
use App\Filament\Resources\Challenges\Pages\Concerns\HasChallengeRecordHeader;
use App\Filament\Resources\Challenges\Widgets\ChallengeQuickGlance;
use App\Models\Enrollment;
use App\Models\Fulfillment;
use App\Models\PuzzleProgress;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChallengeMedalStatus extends ManageRelatedRecords
{
    use HasChallengeRecordHeader;

    protected static string $resource = ChallengeResource::class;

    protected static string $relationship = 'enrollments';

    protected static ?string $navigationLabel = 'Medal Status';

    protected static ?string $title = 'Medal Fulfillment Status';

    public function table(Table $table): Table
    {
        $challengeId = $this->getOwnerRecord()->id;
        $this->getOwnerRecord()->loadCount('puzzles');
        $puzzleTotal = max((int) $this->getOwnerRecord()->puzzle_count, 1);

        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->join('fulfillments', 'fulfillments.enrollment_id', '=', 'enrollments.id')
                ->select('enrollments.*')
                ->with('user')
                ->with('fulfillment')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Player')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->state(function (Enrollment $record) use ($challengeId, $puzzleTotal): string {
                        $total = $puzzleTotal;
                        $completed = PuzzleProgress::query()
                            ->where('user_id', $record->user_id)
                            ->where('challenge_id', $challengeId)
                            ->whereNotNull('solved_at')
                            ->count();
                        $percent = round(($completed / $total) * 100);

                        return $percent . '%';
                    })
                    ->badge(),
                TextColumn::make('fulfillment.address_snapshot')
                    ->label('Address')
                    ->state(function (Enrollment $record): string {
                        $address = $record->fulfillment?->address_snapshot;

                        if (! is_array($address)) {
                            return '-';
                        }

                        $parts = array_filter([
                            $address['address_line1'] ?? null,
                            $address['address_line2'] ?? null,
                            $address['city'] ?? null,
                            $address['state'] ?? null,
                            $address['postcode'] ?? null,
                            $address['country'] ?? null,
                        ], fn ($part) => filled($part));

                        return empty($parts) ? '-' : implode(', ', $parts);
                    })
                    ->wrap()
                    ->toggleable(),
                SelectColumn::make('fulfillment_status')
                    ->label('Shipment Status')
                    ->state(fn (Enrollment $record): string => $record->fulfillment?->status ?? '')
                    ->options([
                        'pending' => 'Pending',
                        'ready_to_ship' => 'Ready to Ship',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                    ])
                    ->selectablePlaceholder(false)
                    ->updateStateUsing(function (string $state, Enrollment $enrollment): string {
                        $fulfillment = $enrollment->fulfillment;

                        if (! $fulfillment) {
                            Notification::make()
                                ->title('No fulfillment record')
                                ->body('This enrollment does not have a fulfillment record yet.')
                                ->danger()
                                ->send();

                            return '';
                        }

                        $fulfillment->status = $state;

                        if ($state === 'shipped') {
                            $fulfillment->shipped_at ??= now();
                        }

                        if ($state === 'delivered') {
                            $fulfillment->delivered_at ??= now();
                        }

                        $fulfillment->save();

                        return $fulfillment->status;
                    }),
                TextInputColumn::make('fulfillment_courier')
                    ->label('Courier')
                    ->placeholder('Courier')
                    ->state(fn (Enrollment $record): string => $record->fulfillment?->courier ?? '')
                    ->updateStateUsing(function (?string $state, Enrollment $enrollment): ?string {
                        $fulfillment = $enrollment->fulfillment;
                        if ($fulfillment) {
                            $fulfillment->courier = $state;
                            $fulfillment->save();
                        }

                        return $fulfillment?->courier;
                    }),
                TextInputColumn::make('fulfillment_tracking_number')
                    ->label('Tracking #')
                    ->placeholder('Tracking Number')
                    ->state(fn (Enrollment $record): string => $record->fulfillment?->tracking_number ?? '')
                    ->updateStateUsing(function (?string $state, Enrollment $enrollment): ?string {
                        $fulfillment = $enrollment->fulfillment;
                        if ($fulfillment) {
                            $fulfillment->tracking_number = $state;
                            $fulfillment->save();
                        }

                        return $fulfillment?->tracking_number;
                    }),
                TextInputColumn::make('fulfillment_tracking_url')
                    ->label('Tracking URL')
                    ->placeholder('Tracking URL')
                    ->state(fn (Enrollment $record): string => $record->fulfillment?->tracking_url ?? '')
                    ->updateStateUsing(function (?string $state, Enrollment $enrollment): ?string {
                        $fulfillment = $enrollment->fulfillment;
                        if ($fulfillment) {
                            $fulfillment->tracking_url = $state;
                            $fulfillment->save();
                        }

                        return $fulfillment?->tracking_url;
                    }),
            ])
            ->filters([
                SelectFilter::make('fulfillment_status')
                    ->label('Shipment Status')
                    ->options([
                        'completed' => 'Enrollment Completed',
                        'ready_to_ship' => 'Ready to Ship',
                        'shipped' => 'Shipped',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === 'completed') {
                            return $query->where('enrollments.status', 'completed');
                        }

                        if (in_array($value, ['ready_to_ship', 'shipped'], true)) {
                            return $query->whereHas('fulfillment', fn ($q) => $q->where('status', $value));
                        }

                        return $query;
                    }),
                SelectFilter::make('completion_bucket')
                    ->options([
                        'almost' => 'Almost Complete (80%+)',
                        'complete' => 'Fully Complete (100%)',
                    ])
                    ->query(function ($query, array $data) use ($challengeId, $puzzleTotal) {
                        $value = $data['value'] ?? null;

                        if (! in_array($value, ['almost', 'complete'], true)) {
                            return $query;
                        }

                        $total = $puzzleTotal;
                        $threshold = $value === 'complete' ? $total : (int) ceil($total * 0.8);

                        return $query->whereHas('user', function ($q) use ($challengeId, $threshold) {
                            $q->whereRaw(
                                '(SELECT COUNT(*) FROM puzzle_progress WHERE puzzle_progress.user_id = users.id AND puzzle_progress.challenge_id = ? AND puzzle_progress.solved_at IS NOT NULL) >= ?',
                                [$challengeId, $threshold]
                            );
                        });
                    }),
            ])
            ->defaultSort('enrollments.updated_at', 'desc');
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            ChallengeQuickGlance::class,
        ];
    }
}
