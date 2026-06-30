<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\UserActivityLog;
use App\Filament\Resources\Users\Pages\UserEnrollments;
use App\Filament\Resources\Users\Pages\UserOrders;
use App\Filament\Resources\Users\Pages\UserPuzzleProgress;
use App\Filament\Resources\Users\Pages\UserStickers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
            'orders' => UserOrders::route('/{record}/orders'),
            'enrollments' => UserEnrollments::route('/{record}/enrollments'),
            'progress' => UserPuzzleProgress::route('/{record}/progress'),
            'medals' => UserStickers::route('/{record}/medals'),
            'activity' => UserActivityLog::route('/{record}/activity'),
        ];
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewUser::class,
            EditUser::class,
            UserOrders::class,
            UserEnrollments::class,
            UserPuzzleProgress::class,
            UserStickers::class,
            UserActivityLog::class,
        ]);
    }
}
