<?php

namespace App\Filament\Resources\Challenges\Pages;

use App\Filament\Resources\Challenges\ChallengeResource;
use App\Filament\Resources\Challenges\Pages\Concerns\HasChallengeRecordHeader;
use App\Filament\Resources\Challenges\Widgets\ChallengeQuickGlance;
use App\Models\Enrollment;
use App\Models\PuzzleProgress;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChallengePlayers extends ManageRelatedRecords
{
    use HasChallengeRecordHeader;

    protected static string $resource = ChallengeResource::class;

    protected static string $relationship = 'enrollments';

    protected static ?string $navigationLabel = 'Players';

    protected static ?string $title = 'Challenge Players';

    public function table(Table $table): Table
    {
        $challengeId = $this->getOwnerRecord()->id;
        $this->getOwnerRecord()->loadCount('puzzles');
        $puzzleTotal = max((int) $this->getOwnerRecord()->puzzle_count, 1);

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Player')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Challenge Start Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (Enrollment $record) use ($challengeId, $puzzleTotal): string {
                        $total = $puzzleTotal;
                        $completed = PuzzleProgress::query()
                            ->where('user_id', $record->user_id)
                            ->where('challenge_id', $challengeId)
                            ->whereNotNull('solved_at')
                            ->count();
                        $percent = round(($completed / $total) * 100);

                        return $completed . ' / ' . $total . ' (' . $percent . '%)';
                    }),
            ])
            ->defaultSort('created_at', 'desc');
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
