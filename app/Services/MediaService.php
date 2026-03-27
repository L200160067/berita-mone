<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MediaService
{
    /**
     * Handle media from file upload (UploadedFile), a path string, or an external URL.
     * Stores to public/uploads/ or GitHub (if configured).
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
        return $this->convertPathToWebp($file->getRealPath(), $file->getMimeType() ?: 'image/jpeg');
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
        // 1. Convert to local WebP first (in temp storage)
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename    = time().'_'.uniqid().'.webp';
        $destination = $tempDir.'/'.$filename;

        $this->convertToWebp($tempPath, $destination, $mime);

        // 2. Check if GitHub config is active
        $rawRepo = config('services.github_asset.repo');
        // Clean up URL just in case user provides full HTTPS/github.io link
        $githubRepo = str_ireplace(
            ['https://github.com/', 'http://github.com/', 'https://', 'http://', '.github.io'],
            '',
            (string) $rawRepo
        );
        $githubRepo = trim($githubRepo, '/');

        $githubToken = config('services.github_asset.token');

        if (! empty($githubRepo) && ! empty($githubToken)) {
            $githubBranch = config('services.github_asset.branch', 'main');
            
            // Read file content and encode to Base64
            $fileContent   = file_get_contents($destination);
            $base64Content = base64_encode($fileContent);

            $path = 'images/blog/' . $filename; // Path inside the github repo

            $response = Http::withToken($githubToken)
                ->put("https://api.github.com/repos/{$githubRepo}/contents/{$path}", [
                    'message' => "Upload image via Filament: {$filename}",
                    'content' => $base64Content,
                    'branch'  => $githubBranch,
                ]);

            if ($response->successful()) {
                // Delete the temporary local webp file to save space
                @unlink($destination);

                // Construct raw URL so it serves as an image directly
                $rawUrl = "https://raw.githubusercontent.com/{$githubRepo}/{$githubBranch}/{$path}";

                return [
                    'filename' => $rawUrl,
                    'url'      => $rawUrl,
                    'thumb'    => $rawUrl,
                ];
            }
            
            // If GitHub API fails, fall through to local upload as fallback.
        }

        // --- FALLBACK (Local CWP Upload) ---
        $uploadsDir = public_path('uploads/posts');

        if (! is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $finalDestination = $uploadsDir.'/'.$filename;
        rename($destination, $finalDestination);

        $baseUrl = config('app.url');

        return [
            'filename' => 'posts/'.$filename,
            'url'      => rtrim($baseUrl, '/')."/uploads/posts/{$filename}",
            'thumb'    => rtrim($baseUrl, '/')."/uploads/posts/{$filename}",
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
            'url'   => null,
            'thumb' => null,
        ];
    }
}
