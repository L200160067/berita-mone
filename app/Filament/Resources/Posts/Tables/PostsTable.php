<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use App\Services\ArticleGeneratorService;
use App\Services\MediaService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->sortable()
                    ->label('Category'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'review' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('author')->searchable(),
                TextColumn::make('published_at')->dateTime()->sortable(),
                TextColumn::make('views_total')->numeric()->sortable(),
            ])
            ->filters([])
            ->headerActions([
                // CWP-SAFE: Runs SYNCHRONOUSLY — no queue worker needed
                Action::make('generate')
                    ->label('Generate Article')
                    ->form([
                        TextInput::make('topic')->required(),
                        TextInput::make('location')->required(),
                        TextInput::make('goal')->required(),
                        Textarea::make('impact'),
                    ])
                    ->action(function (array $data) {
                        // Idempotency check
                        $hash = md5(json_encode($data));
                        if (Post::where('payload_hash', $hash)->exists()) {
                            Notification::make()
                                ->title('Artikel duplikat terdeteksi, dibatalkan.')
                                ->warning()->send();

                            return;
                        }

                        // Generate article content (Gemini AI or fallback template)
                        $generator = app(ArticleGeneratorService::class);
                        $articleData = $generator->generate($data);

                        // Handle media (default image — no Upload at generation time)
                        $media = app(MediaService::class)->handle(null, null);

                        $source = $articleData['source'] ?? 'template';
                        $dbData = array_diff_key($articleData, ['source' => true]);

                        Post::create([
                            ...$dbData,
                            'status' => 'draft',
                            'payload_hash' => $hash,
                            'image_source' => 'url',
                            'cover_url' => $media['url'],
                            'cover_thumb' => $media['thumb'],
                        ]);

                        $isAi = $source === 'groq';
                        Notification::make()
                            ->title($isAi ? '✨ Artikel digenerate oleh AI (Groq).' : '📄 Artikel digenerate via template fallback.')
                            ->body($isAi ? 'Konten natural dari Groq API berhasil disimpan sebagai draft.' : 'Groq API tidak tersedia, template statikal digunakan.')
                            ->success()->send();
                    })
                    ->color('success')
                    ->icon('heroicon-o-sparkles'),
            ])
            ->recordActions([
                EditAction::make(),
                // Manual publish toggle — CWP has no scheduler
                Action::make('togglePublish')
                    ->label(fn ($record) => $record->status === 'published' ? 'Unpublish' : 'Publish')
                    ->color(fn ($record) => $record->status === 'published' ? 'danger' : 'success')
                    ->icon('heroicon-o-globe-alt')
                    ->action(function ($record) {
                        $newStatus = $record->status === 'published' ? 'draft' : 'published';
                        $record->update([
                            'status' => $newStatus,
                            'published_at' => $newStatus === 'published' ? now() : $record->published_at,
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
