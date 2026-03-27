<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Services\MediaService;
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
                                'url'    => 'URL',
                            ])
                            ->default('upload')
                            ->reactive(),

                        // Preview gambar yang sudah tersimpan (edit mode)
                        \Filament\Forms\Components\Placeholder::make('cover_preview')
                            ->label('Current Cover Image')
                            ->content(function ($record) {
                                $url = $record?->cover_url;
                                if (empty($url)) {
                                    return new \Illuminate\Support\HtmlString('<span class="text-gray-400 text-sm italic">Belum ada gambar tersimpan.</span>');
                                }

                                return new \Illuminate\Support\HtmlString(
                                    '<img src="'.e($url).'" style="max-height:200px;max-width:100%;border-radius:8px;object-fit:cover;" />'
                                );
                            })
                            ->visible(fn (Get $get) => $get('image_source') === 'upload'),

                        // FileUpload menyimpan ke disk 'uploads' → diproses di mutateFormDataBeforeSave
                        FileUpload::make('cover_upload')
                            ->label('Upload New Image')
                            ->image()
                            ->imagePreviewHeight('180')
                            ->directory('temp-uploads')
                            ->disk('uploads')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(5120)
                            ->visible(fn (Get $get) => $get('image_source') === 'upload'),

                        TextInput::make('cover_url')
                            ->label('Image URL')
                            ->url()
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
