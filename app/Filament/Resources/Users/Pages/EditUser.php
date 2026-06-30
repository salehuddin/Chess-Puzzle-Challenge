<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Pages\Concerns\HasUserRecordHeader;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use HasUserRecordHeader;

    protected static string $resource = UserResource::class;

    protected static ?string $navigationLabel = 'Edit Account';

    protected static ?string $title = 'Edit User Account';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
