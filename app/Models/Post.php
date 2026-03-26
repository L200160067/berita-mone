<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'author',
        'cover_url',
        'cover_thumb',
        'image_source',
        'status',
        'published_at',
        'views_total',
        'views_daily',
        'payload_hash',
        'is_ai_generated',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted()
    {
        static::saving(function ($post) {
            if (empty($post->category_id)) {
                $uncategorized = \App\Models\Category::firstOrCreate(
                    ['slug' => 'uncategorized'],
                    ['name' => 'Uncategorized']
                );
                $post->category_id = $uncategorized->id;
            }
        });
    }
}
