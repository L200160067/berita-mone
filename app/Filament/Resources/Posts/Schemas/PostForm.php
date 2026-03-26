<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
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
                                'url' => 'URL',
                            ])
                            ->default('upload')
                            ->reactive(),
                        FileUpload::make('cover_upload')
                            ->image()
                            ->directory('posts')
                            ->disk('uploads')
                            ->default(function (?Post $record): ?string {
                                $coverUrl = $record?->cover_url;

                                if (blank($coverUrl)) {
                                    return null;
                                }

                                $path = parse_url($coverUrl, PHP_URL_PATH) ?: $coverUrl; // e.g. /uploads/posts/FILE.jpg
                                $path = ltrim((string) $path, '/');

                                // Convert URL path => state file name relative to disk root.
                                // We expect MediaService format: /uploads/posts/{filename}
                                $relative = Str::after($path, 'uploads/');

                                if (blank($relative)) {
                                    $relative = 'posts/'.basename($path);
                                }

                                return $relative ?: null;
                            })
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                // When user uploads a new file in "Upload" mode,
                                // sync it into the URL columns used by the rest of the app.
                                if (blank($state)) {
                                    return;
                                }

                                $url = rtrim(config('app.url'), '/').'/uploads/'.ltrim((string) $state, '/');

                                $set('cover_url', $url);
                                $set('cover_thumb', $url);
                            })
                            ->visible(fn (Get $get) => $get('image_source') === 'upload'),
                        TextInput::make('cover_url')
                            ->label('Image URL')
                            ->visible(fn (Get $get) => $get('image_source') === 'url'),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'Review',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),
                        DateTimePicker::make('published_at'),
                        TextInput::make('author')->default('Admin'),
                    ]),
            ]);
    }
}
