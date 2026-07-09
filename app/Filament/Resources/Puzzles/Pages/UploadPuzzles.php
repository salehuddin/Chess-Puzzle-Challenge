<?php

namespace App\Filament\Resources\Puzzles\Pages;

use App\Filament\Resources\Puzzles\PuzzleResource;
use Filament\Resources\Pages\Page;

class UploadPuzzles extends Page
{
    protected static string $resource = PuzzleResource::class;

    protected string $view = 'filament.resources.puzzles.pages.upload-puzzles';

    protected static ?string $title = 'Upload Puzzle Batch';

    protected static ?string $navigationLabel = 'Upload Batch';
}
