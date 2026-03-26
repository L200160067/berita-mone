<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class WarmupCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $postId) {}

    public function handle()
    {
        $post = Post::find($this->postId);
        if (! $post) {
            return;
        }

        // Invalidate list cache
        Cache::forget('posts:list');

        // Warm up specific post cache (valid for 1 hour)
        Cache::put("posts:slug:{$post->slug}", clone $post, now()->addMinutes(60));
    }
}
