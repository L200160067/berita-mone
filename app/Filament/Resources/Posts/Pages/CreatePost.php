<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Services\MediaService;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['image_source'] === 'upload' && !empty($data['cover_upload'])) {
            $mediaService = app(MediaService::class);
            // $data['cover_upload'] contains the path relative to the 'uploads' disk (public/uploads)
            $tempPath = public_path('uploads/' . $data['cover_upload']);
            
            if (file_exists($tempPath)) {
                $mime = mime_content_type($tempPath) ?: 'image/jpeg';
                $result = $mediaService->convertPathToWebp($tempPath, $mime);
                
                $data['cover_url'] = $result['url'];
                $data['cover_thumb'] = $result['thumb'];
                
                // Cleanup temp file uploaded by Filament
                @unlink($tempPath);
            }
        }

        unset($data['cover_upload']);

        return $data;
    }
}
