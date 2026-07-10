<?php

namespace App\Filament\Resources\Challenges\Pages;

use App\Filament\Resources\Challenges\ChallengeResource;
use App\Filament\Resources\Challenges\Pages\Concerns\HasChallengeRecordHeader;
use App\Models\PuzzleProgress;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChallengeAnalytics extends EditRecord
{
    use HasChallengeRecordHeader;

    protected static string $resource = ChallengeResource::class;

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $title = 'Challenge Analytics';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filters')
                    ->schema([
                        DatePicker::make('analytics_from')
                            ->label('From Date')
                            ->default(now()->subDays(29)->toDateString())
                            ->live(),
                        DatePicker::make('analytics_to')
                            ->label('To Date')
                            ->default(now()->toDateString())
                            ->live(),
                    ])
                    ->columns(2),
                Section::make('Sign Ups')
                    ->schema([
                        ViewField::make('signup_chart')
                            ->view('filament.challenges.signups-chart')
                            ->viewData(fn (): array => [
                                'chart' => $this->getSignupChartPayload(),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('KPI Snapshot')
                    ->schema([
                        Placeholder::make('total_players')
                            ->label('Total Players')
                            ->content(fn (): string => number_format($this->getPlayerCount())),
                        Placeholder::make('estimated_revenue_usd')
                            ->label('Amount Paid (USD)')
                            ->content(fn (): string => '$'.number_format($this->getPlayerCount() * (float) $this->getRecord()->price_usd, 2)),
                        Placeholder::make('completed_count')
                            ->label('Completed')
                            ->content(fn (): string => number_format($this->getStatusCount(['completed']))),
                        Placeholder::make('avg_completed_puzzles')
                            ->label('Average Completed Puzzles')
                            ->content(fn (): string => number_format($this->getAverageCompletedPuzzles(), 1)),
                    ])
                    ->columns(4),
            ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    /**
     * @return array{from: Carbon, to: Carbon}
     */
    protected function getDateRange(): array
    {
        $fromRaw = $this->data['analytics_from'] ?? now()->subDays(29)->toDateString();
        $toRaw = $this->data['analytics_to'] ?? now()->toDateString();

        $from = Carbon::parse($fromRaw)->startOfDay();
        $to = Carbon::parse($toRaw)->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return ['from' => $from, 'to' => $to];
    }

    protected function applyDateRange($query)
    {
        ['from' => $from, 'to' => $to] = $this->getDateRange();

        return $query->whereBetween('enrollments.created_at', [$from, $to]);
    }

    protected function getPlayerCount(): int
    {
        return $this->applyDateRange(
            $this->getRecord()->enrollments()->whereIn('status', ['active', 'completed'])
        )->count();
    }

    protected function getStatusCount(array $statuses): int
    {
        return $this->applyDateRange(
            $this->getRecord()->enrollments()->whereIn('status', $statuses)
        )->count();
    }

    protected function getAverageCompletedPuzzles(): float
    {
        $enrollments = $this->applyDateRange(
            $this->getRecord()->enrollments()->whereIn('status', ['active', 'completed'])
        )->get();

        if ($enrollments->isEmpty()) {
            return 0;
        }

        $challengeId = $this->getRecord()->id;
        $total = 0;
        $count = 0;

        foreach ($enrollments as $enrollment) {
            $solved = PuzzleProgress::query()
                ->where('user_id', $enrollment->user_id)
                ->where('challenge_id', $challengeId)
                ->whereNotNull('solved_at')
                ->count();
            $total += $solved;
            $count++;
        }

        return $count > 0 ? (float) ($total / $count) : 0;
    }

    /**
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    protected function getSignupChartPayload(): array
    {
        ['from' => $from, 'to' => $to] = $this->getDateRange();

        $rows = $this->getRecord()->enrollments()
            ->selectRaw('DATE(created_at) as signup_date, COUNT(*) as total')
            ->whereBetween('enrollments.created_at', [$from, $to])
            ->groupBy('signup_date')
            ->orderBy('signup_date')
            ->pluck('total', 'signup_date');

        $labels = [];
        $values = [];

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $date) {
            $key = $date->toDateString();
            $labels[] = $date->format('d M');
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Daily Sign Ups',
                    'data' => $values,
                    'borderColor' => '#15803d',
                    'backgroundColor' => 'rgba(21, 128, 61, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }
}
