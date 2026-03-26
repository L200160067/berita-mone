<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\ArticleGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public function __construct(public array $data) {}

    public function handle(ArticleGeneratorService $generator)
    {
        // 1. Idempotency Check
        $hash = md5(json_encode($this->data));

        if (Post::where('payload_hash', $hash)->exists()) {
            Log::info("Idempotency triggered: Duplicate article skipped for hash {$hash}");

            return;
        }

        // 2. Generate Basic Content
        $articleData = $generator->generate($this->data);

        // 3. Save as DRAFT (with Idempotency Hash)
        $post = Post::create([
            ...$articleData,
            'status' => 'draft',
            'payload_hash' => $hash,
            'image_source' => 'upload', // Default placeholder
        ]);

        // 4. Dispatch Media Job separately
        // Taking the cover URL/upload input if the original data had it (handled dynamically via MediaService later, but $this->data doesn't hold Media yet. Wait! The Filament Form did not attach `cover_upload` to the Table header action `Action::make('generate')`!).
        // Note: The form only had topic, location, goal, impact. So we will rely on default media via MediaService.
        ProcessMediaJob::dispatch($post->id, null, null);
    }
}
