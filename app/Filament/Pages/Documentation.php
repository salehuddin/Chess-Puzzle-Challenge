<?php

namespace App\Filament\Pages;

use App\Services\DocumentationService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Request;

class Documentation extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Documentation';

    protected static ?string $title = 'Documentation';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.documentation';

    public function mount(DocumentationService $service): void
    {
        $document = $service->findDocument(Request::query('doc', ''));

        if ($document === null) {
            $document = $service->defaultDocument();
        }

        $this->selectedDocument = $document;
    }

    public ?array $selectedDocument = null;

    public function getViewData(): array
    {
        $service = app(DocumentationService::class);

        return [
            'categories' => $service->categories(),
            'selectedDocument' => $this->selectedDocument,
        ];
    }
}
