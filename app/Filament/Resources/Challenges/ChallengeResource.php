<?php

namespace App\Filament\Resources\Challenges;

use App\Filament\Resources\Challenges\Pages\AttachPuzzles;
use App\Filament\Resources\Challenges\Pages\ChallengeAnalytics;
use App\Filament\Resources\Challenges\Pages\ChallengeContent;
use App\Filament\Resources\Challenges\Pages\ChallengeMedalStatus;
use App\Filament\Resources\Challenges\Pages\ChallengePlayers;
use App\Filament\Resources\Challenges\Pages\ChallengePuzzles;
use App\Filament\Resources\Challenges\Pages\CreateChallenge;
use App\Filament\Resources\Challenges\Pages\EditChallenge;
use App\Filament\Resources\Challenges\Pages\ListChallenges;
use App\Filament\Resources\Challenges\Schemas\ChallengeForm;
use App\Filament\Resources\Challenges\Tables\ChallengesTable;
use App\Models\Challenge;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return ChallengeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChallengesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChallenges::route('/'),
            'create' => CreateChallenge::route('/create'),
            'analytics' => ChallengeAnalytics::route('/{record}/analytics'),
            'details' => EditChallenge::route('/{record}/details'),
            'content' => ChallengeContent::route('/{record}/content'),
            'puzzles' => ChallengePuzzles::route('/{record}/puzzles'),
            'attach-puzzles' => AttachPuzzles::route('/{record}/attach-puzzles'),
            'players' => ChallengePlayers::route('/{record}/players'),
            'medal-status' => ChallengeMedalStatus::route('/{record}/medal-status'),
            'edit' => EditChallenge::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ChallengeAnalytics::class,
            EditChallenge::class,
            ChallengeContent::class,
            ChallengePuzzles::class,
            ChallengePlayers::class,
            ChallengeMedalStatus::class,
        ]);
    }
}
