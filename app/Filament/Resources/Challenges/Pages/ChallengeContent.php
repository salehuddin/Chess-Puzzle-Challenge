<?php

namespace App\Filament\Resources\Challenges\Pages;

use App\Filament\Resources\Challenges\ChallengeResource;
use App\Filament\Resources\Challenges\Pages\Concerns\HasChallengeRecordHeader;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChallengeContent extends EditRecord
{
    use HasChallengeRecordHeader;

    protected static string $resource = ChallengeResource::class;

    protected static ?string $navigationLabel = 'Content';

    protected static ?string $title = 'Challenge Content';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Main Content')
                    ->description('Editor.js content is stored as JSON for flexible frontend rendering.')
                    ->schema([
                        ViewField::make('content_blocks')
                            ->view('filament.forms.components.editorjs')
                            ->columnSpanFull(),
                    ]),
                Section::make('Media')
                    ->schema([
                        FileUpload::make('image_gallery')
                            ->label('Image Gallery')
                            ->disk('public')
                            ->image()
                            ->multiple()
                            ->directory('artworks/challenges/gallery')
                            ->reorderable()
                            ->columnSpanFull(),
                        Repeater::make('videos')
                            ->schema([
                                TextInput::make('title')
                                    ->required(),
                                TextInput::make('url')
                                    ->required()
                                    ->url(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make('Terms & Conditions')
                    ->schema([
                        RichEditor::make('terms_and_conditions')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('FAQ')
                    ->description('Optional. Add question/answer pairs shown in the FAQ accordion on the public challenge page.')
                    ->schema([
                        Repeater::make('faq')
                            ->label('')
                            ->schema([
                                TextInput::make('question')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('answer')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
