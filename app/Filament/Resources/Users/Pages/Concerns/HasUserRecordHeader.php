<?php

namespace App\Filament\Resources\Users\Pages\Concerns;

use App\Filament\Resources\Users\UserResource;

trait HasUserRecordHeader
{
    public function getHeading(): string
    {
        return (string) $this->getRecord()->name;
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            UserResource::getUrl('index') => 'Users',
            UserResource::getUrl('view', ['record' => $this->getRecord()]) => 'View',
            (string) $this->getRecord()->name,
        ];
    }

    public function getSubheading(): ?string
    {
        return (string) $this->getRecord()->email;
    }

    public function getTabPageHeading(): string
    {
        return static::getNavigationLabel();
    }
}
