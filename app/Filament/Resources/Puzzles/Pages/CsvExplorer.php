<?php

namespace App\Filament\Resources\Puzzles\Pages;

use App\Filament\Resources\Puzzles\PuzzleResource;
use Filament\Resources\Pages\Page;

class CsvExplorer extends Page
{
    protected static string $resource = PuzzleResource::class;

    protected string $view = 'filament.resources.puzzles.pages.csv-explorer';

    protected static ?string $title = 'CSV Puzzle Explorer';

    protected static ?string $navigationLabel = 'CSV Explorer';
}
