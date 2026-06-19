<?php

namespace App\Filament\Resources\Challenges\Pages\Concerns;

use App\Filament\Resources\Challenges\ChallengeResource;

trait HasChallengeRecordHeader
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
            ChallengeResource::getUrl('index') => 'Challenges',
            ChallengeResource::getUrl('details', ['record' => $this->getRecord()]) => 'Edit',
            (string) $this->getRecord()->name,
        ];
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getTabPageHeading(): string
    {
        return static::getNavigationLabel();
    }
}
