<?php

namespace App\Console\Commands;

use App\Jobs\WarmupCacheJob;
use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-scheduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes posts that have reached their scheduled publish date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $posts = Post::where('status', 'draft')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        foreach ($posts as $post) {
            $post->update(['status' => 'published']);
            WarmupCacheJob::dispatch($post->id);
            $this->info("Published post ID: {$post->id}");
        }
    }
}
