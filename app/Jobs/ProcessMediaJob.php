<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 60;

    public function __construct(
        public int $postId,
        public ?string $uploadPath = null,
        public ?string $url = null
    ) {}

    public function handle(MediaService $media)
    {
        $post = Post::find($this->postId);
        if (! $post) {
            return;
        }

        $result = $media->handle($this->uploadPath, $this->url);

        $post->update([
            'cover_url' => $result['url'],
            'cover_thumb' => $result['thumb'],
        ]);
    }
}
