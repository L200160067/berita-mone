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

        $filename = time().'_'.uniqid().'.webp';
        $destination = $uploadsDir.'/'.$filename;

        $this->convertToWebp($file->getRealPath(), $destination, $file->getMimeType());

        $baseUrl = config('app.url');

        return [
            'url'   => "{$baseUrl}/uploads/posts/{$filename}",
            'thumb' => "{$baseUrl}/uploads/posts/{$filename}",
        ];
    }

    /**
     * Convert any image (JPEG, PNG, GIF, WebP) to WebP using PHP GD.
     * Falls back to plain copy if GD is unavailable or mime is unsupported.
     */
    private function convertToWebp(string $sourcePath, string $destPath, string $mime): void
    {
        if (! function_exists('imagewebp')) {
            // GD not available — store original without conversion
            copy($sourcePath, $destPath);

            return;
        }

        $image = match (true) {
            str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => @imagecreatefromjpeg($sourcePath),
            str_contains($mime, 'png')                                 => @imagecreatefrompng($sourcePath),
            str_contains($mime, 'gif')                                 => @imagecreatefromgif($sourcePath),
            str_contains($mime, 'webp')                                => @imagecreatefromwebp($sourcePath),
            default                                                     => false,
        };

        if (! $image) {
            // Unsupported type — store as-is
            copy($sourcePath, $destPath);

            return;
        }

        // Preserve transparency for PNG/GIF
        if (str_contains($mime, 'png') || str_contains($mime, 'gif')) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        imagewebp($image, $destPath, 80);
        imagedestroy($image);
    }

    /**
     * Convert a local file path on disk to WebP and return URL array.
     * Used by Filament FileUpload's saveUploadedFileUsing callback.
     */
    public function convertPathToWebp(string $tempPath, string $mime): array
    {
        $uploadsDir = public_path('uploads/posts');

        if (! is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $filename    = time().'_'.uniqid().'.webp';
        $destination = $uploadsDir.'/'.$filename;

        $this->convertToWebp($tempPath, $destination, $mime);

        $baseUrl = config('app.url');

        return [
            'filename' => 'posts/'.$filename,
            'url'      => "{$baseUrl}/uploads/posts/{$filename}",
            'thumb'    => "{$baseUrl}/uploads/posts/{$filename}",
        ];
    }

    private function fromUrl(string $url): array
    {
        return [
            'url'   => $url,
            'thumb' => $url,
        ];
    }

    private function default(): array
    {
        return [
            'url'   => config('app.url').'/uploads/default.jpg',
            'thumb' => config('app.url').'/uploads/default.jpg',
        ];
    }
}
