<?php

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Rate-limit public endpoints (120 req/min per IP)
Route::middleware('throttle:120,1')->group(function () {

    /**
     * GET /api/posts
     * Returns published posts (without full content).
     * File-cached for 60 seconds — CWP-safe (no Redis needed).
     */
    Route::get('/posts', function () {
        $data = Cache::remember('posts:list', 60, function () {
            return Post::where('status', 'published')
                ->select('id', 'title', 'slug', 'excerpt', 'cover_url', 'cover_thumb', 'author', 'published_at')
                ->with(['category:id,name,slug'])
                ->latest('published_at')
                ->get();
        });

        return response()->json(['success' => true, 'data' => $data])
            ->header('Access-Control-Allow-Origin', 'https://mone.mutudev.com');
    });

    /**
     * GET /api/posts/{slug}
     * Returns full article detail, increments view counter.
     */
    Route::get('/posts/{slug}', function (string $slug) {
        $post = Cache::remember("posts:slug:{$slug}", 60, function () use ($slug) {
            return Post::where('slug', $slug)
                ->where('status', 'published')
                ->with(['category:id,name,slug'])
                ->first();
        });

        if (! $post) {
            return response()->json(['success' => false, 'message' => 'Post not found.'], 404);
        }

        // Increment view counters (lightweight, runs on read)
        $post->increment('views_total');
        $post->increment('views_daily');

        return response()->json(['success' => true, 'data' => $post->fresh()])
            ->header('Access-Control-Allow-Origin', 'https://mone.mutudev.com');
    });

});
