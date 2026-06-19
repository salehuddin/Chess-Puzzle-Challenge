<x-filament-panels::page>
    <form wire:submit="dispatchImport">
        {{ $this->form }}
        
        <x-filament::button type="submit" class="mt-4" color="success" size="lg">
            Launch Ingestion Pipeline
        </x-filament::button>
    </form>
</x-filament-panels::page>
