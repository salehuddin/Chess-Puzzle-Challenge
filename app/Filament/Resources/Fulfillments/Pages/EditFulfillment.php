<?php

namespace App\Filament\Resources\Fulfillments\Pages;

use App\Filament\Resources\Fulfillments\FulfillmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFulfillment extends EditRecord
{
    protected static string $resource = FulfillmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
