<?php

namespace App\Filament\Resources\Enrollments\Pages;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Services\CommerceHierarchyService;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function afterCreate(): void
    {
        app(CommerceHierarchyService::class)->syncFulfillmentForEnrollment($this->record);
    }
}
