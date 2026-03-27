<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Services\MediaService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // No longer needed to hydrate virtual field cover_upload
        // The Placeholder component already displays 'cover_url' natively!
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['image_source'] === 'upload' && !empty($data['cover_upload'])) {
            $mediaService = app(MediaService::class);
            $tempPath = public_path('uploads/' . $data['cover_upload']);
            
            if (file_exists($tempPath)) {
                $mime = mime_content_type($tempPath) ?: 'image/jpeg';
                $result = $mediaService->convertPathToWebp($tempPath, $mime);
                
                $data['cover_url'] = $result['url'];
                $data['cover_thumb'] = $result['thumb'];
                
                // Cleanup temp file
                @unlink($tempPath);
            }
        } elseif (empty($data['cover_url'])) {
            // retain previous url if no new file is uploaded
            $data['cover_url'] = $this->record->cover_url;
            $data['cover_thumb'] = $this->record->cover_thumb;
        }

        unset($data['cover_upload']);

        return $data;
    }
}
