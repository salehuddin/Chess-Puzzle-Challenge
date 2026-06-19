<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Bundle;
use App\Models\Challenge;
use App\Services\CommerceHierarchyService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @var array<int, array{item_type: string, item_id: int}>|null
     */
    protected ?array $purchasableSelections = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $data['items'] ?? [];

        if (blank($items)) {
            throw ValidationException::withMessages([
                'items' => 'At least one item is required.',
            ]);
        }

        $selections = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $itemType = (string) ($item['item_type'] ?? 'challenge');
            $itemId = (int) ($item['item_id'] ?? 0);

            if ($itemId < 1) {
                throw ValidationException::withMessages([
                    'items' => 'Each line item must have a selected challenge or bundle.',
                ]);
            }

            $model = $itemType === 'bundle'
                ? Bundle::query()->find($itemId)
                : Challenge::query()->find($itemId);

            if (! $model) {
                throw ValidationException::withMessages([
                    'items' => "The selected {$itemType} was not found.",
                ]);
            }

            $subtotal += (float) ($model->price_usd ?? 0);

            $selections[] = [
                'item_type' => $itemType,
                'item_id' => $itemId,
            ];
        }

        $this->purchasableSelections = $selections;

        $data['subtotal_amount'] = $subtotal;
        $data['total_amount'] = $subtotal - (float) ($data['discount_amount'] ?? 0);
        $data['currency'] = $data['currency'] ?? 'USD';

        return Arr::except($data, ['items']);
    }

    protected function afterCreate(): void
    {
        if (! $this->purchasableSelections) {
            return;
        }

        app(CommerceHierarchyService::class)->syncFromSelections(
            $this->record,
            $this->purchasableSelections,
        );

        Notification::make()
            ->title('Order hierarchy synced')
            ->body('Order items created and downstream enrollments were generated where eligible.')
            ->success()
            ->send();
    }
}
