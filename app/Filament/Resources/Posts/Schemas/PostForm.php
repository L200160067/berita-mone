<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use App\Services\MediaService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Set $set, ?string $state, $livewire): void {
                                if (! $livewire instanceof CreateRecord) {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('excerpt')
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->columnSpanFull(),
                    ]),
                Section::make('Media & Status')
                    ->schema([
                        Select::make('image_source')
                            ->options([
                                'upload' => 'Upload',
                                'url'    => 'URL',
                            ])
                            ->default('upload')
                            ->reactive(),

                        // Hidden fields — persist cover URL to DB on every save.
                        Hidden::make('cover_url'),
                        Hidden::make('cover_thumb'),

                        FileUpload::make('cover_upload')
                            ->label('Cover Image')
                            ->image()
                            ->imagePreviewHeight('200')
                            ->directory('posts')
                            ->disk('uploads')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            // Do NOT persist this virtual field to the DB.
                            ->dehydrated(false)
                            // Convert uploaded file to WebP via MediaService.
                            ->saveUploadedFileUsing(function ($file, Set $set): string {
                                /** @var \App\Services\MediaService $media */
                                $media  = app(MediaService::class);
                                $result = $media->convertPathToWebp(
                                    $file->getRealPath(),
                                    $file->getMimeType() ?: 'image/jpeg',
                                );

                                // Sync cover URL columns so they are saved to DB.
                                $set('cover_url', $result['url']);
                                $set('cover_thumb', $result['thumb']);

                                // Return the relative path for FileUpload state (preview).
                                return $result['filename']; // e.g. "posts/1234_abc.webp"
                            })
                            ->visible(fn (Get $get) => $get('image_source') === 'upload'),

                        TextInput::make('cover_url')
                            ->label('Image URL')
                            ->visible(fn (Get $get) => $get('image_source') === 'url')
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $set('cover_thumb', $state);
                            }),

                        Select::make('status')
                            ->options([
                                'draft'     => 'Draft',
                                'review'    => 'Review',
                                'published' => 'Published',
                                'archived'  => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),
                        DateTimePicker::make('published_at'),
                        TextInput::make('author')->default('Admin'),
                    ]),
            ]);
    }
}
