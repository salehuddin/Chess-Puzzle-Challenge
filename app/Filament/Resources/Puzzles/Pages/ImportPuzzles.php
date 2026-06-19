<?php

namespace App\Filament\Resources\Puzzles\Pages;

use App\Filament\Resources\Puzzles\PuzzleResource;
use App\Jobs\ImportLichessPuzzlesJob;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;

class ImportPuzzles extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PuzzleResource::class;

    protected string $view = 'filament.resources.puzzles.pages.import-puzzles';

    protected static ?string $title = 'Import Lichess Puzzles';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'path' => 'lichess_db_puzzle.csv',
            'min_rating' => null,
            'max_rating' => null,
            'min_popularity' => 80,
            'themes' => '',
            'limit' => 100000,
        ]);
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Section::make('CSV Server Ingestion Settings')
                    ->description('This tool streams massive CSVs straight into your SQL Database securely via worker jobs, allowing safe bypass over conventional HTTP Upload timeouts.')
                    ->schema([
                        TextInput::make('path')
                            ->label('CSV Filename (located inside your /storage/app folder)')
                            ->required()
                            ->default('lichess_db_puzzle.csv')
                            ->helperText('e.g. Place your 2GB lichess payload directly into storage/app over SFTP/FTP.'),
                        TextInput::make('min_rating')
                            ->label('Minimum ELO Rating')
                            ->numeric(),
                        TextInput::make('max_rating')
                            ->label('Maximum ELO Rating')
                            ->numeric(),
                        TextInput::make('min_popularity')
                            ->label('Minimum Hit Popularity Rating')
                            ->numeric()
                            ->default(80)
                            ->helperText('Lichess puzzle quality fluctuates between -100 and +100. Puzzles below 80 are usually visually glitchy.'),
                        TextInput::make('themes')
                            ->label('Enforce Required Themes')
                            ->helperText('e.g. mateIn2, fork. (Provide as comma separated strings). Case sensitive!'),
                        TextInput::make('limit')
                            ->label('Maximum Insertion Limit')
                            ->numeric()
                            ->default(100000)
                            ->helperText('Because Lichess provides 4,000,000 puzzles, you should place a healthy ceiling on your imports to maintain fast Admin loading times.'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function dispatchImport(): void
    {
        $data = $this->form->getState();

        $absolutePath = storage_path('app/'.ltrim($data['path'], '/\\'));

        if (! file_exists($absolutePath)) {
            Notification::make()
                ->title('File Missing!')
                ->body("We could not find the file at: storage/app/{$data['path']}. Ensure you have manually uploaded it there.")
                ->danger()
                ->send();

            return;
        }

        ImportLichessPuzzlesJob::dispatch($absolutePath, $data);

        Notification::make()
            ->title('Ingestion Worker Dispatched!')
            ->body('The background server queue is now meticulously importing your puzzle data! Feel free to leave this page. (Ensure `php artisan queue:work` is live).')
            ->success()
            ->send();
    }
}
