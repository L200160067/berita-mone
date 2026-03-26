<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
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

    /**
     * Inject the existing cover_url into the virtual cover_upload field
     * so Filament can display the current image preview on the edit form.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Derive a relative path from the full URL for the FileUpload state.
        // FileUpload on disk 'uploads' expects path relative to public/uploads/.
        if (! empty($data['cover_url'])) {
            $uploadsBase = rtrim(config('app.url'), '/').'/uploads/';
            $relative    = str_starts_with($data['cover_url'], $uploadsBase)
                ? substr($data['cover_url'], strlen($uploadsBase))
                : null;

            $data['cover_upload'] = $relative; // e.g. "posts/12345_abc.webp"
        }

        return $data;
    }

    /**
     * Ensure cover_url is never wiped when the user saves without uploading a new image.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // cover_upload is a virtual field — remove it from DB payload
        unset($data['cover_upload']);

        // If cover_url was not updated (empty), restore the original value from the record
        if (empty($data['cover_url'])) {
            $data['cover_url']   = $this->record->cover_url;
            $data['cover_thumb'] = $this->record->cover_thumb;
        }

        return $data;
    }
}
