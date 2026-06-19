<?php

namespace App\Filament\Resources\Fulfillments;

use App\Filament\Resources\Fulfillments\Pages\EditFulfillment;
use App\Filament\Resources\Fulfillments\Pages\ListFulfillments;
use App\Filament\Resources\Fulfillments\Schemas\FulfillmentForm;
use App\Filament\Resources\Fulfillments\Tables\FulfillmentsTable;
use App\Models\Fulfillment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FulfillmentResource extends Resource
{
    protected static ?string $model = Fulfillment::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string | UnitEnum | null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return FulfillmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FulfillmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFulfillments::route('/'),
            'edit' => EditFulfillment::route('/{record}/edit'),
        ];
    }
}
