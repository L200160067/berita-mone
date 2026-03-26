<?php

namespace App\Services;

class MediaService
{
    /**
     * Handle media from file upload (UploadedFile), a path string, or an external URL.
     * Stores to public/uploads/ (CWP-safe — no storage:link required).
     */
    public function handle($upload = null, $url = null): array
    {
        if ($upload) {
            return $this->fromUpload($upload);
        }

        if ($url) {
            return $this->fromUrl($url);
        }

        return $this->default();
    }

    private function fromUpload($file): array
    {
        // CWP Rule: use public/uploads/ directly, no Storage::disk('public')
        $uploadsDir = public_path('uploads/posts');

        if (! is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($uploadsDir, $filename);

        $baseUrl = config('app.url');

        return [
            'url' => "{$baseUrl}/uploads/posts/{$filename}",
            'thumb' => "{$baseUrl}/uploads/posts/{$filename}",
        ];
    }

    private function fromUrl(string $url): array
    {
        return [
            'url' => $url,
            'thumb' => $url,
        ];
    }

    private function default(): array
    {
        return [
            'url' => config('app.url').'/uploads/default.jpg',
            'thumb' => config('app.url').'/uploads/default.jpg',
        ];
    }
}
