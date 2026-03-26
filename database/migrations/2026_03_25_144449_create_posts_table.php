<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();

            // Meta & SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            // Media
            $table->string('cover_url')->nullable();
            $table->string('cover_thumb')->nullable();
            $table->string('image_source')->default('upload');

            // Content Lifecycle & Authoring
            $table->string('author')->nullable();
            $table->string('status')->default('draft'); // draft | review | published | archived
            $table->timestamp('published_at')->nullable();

            // Scalability & Growth Engine additions
            $table->unsignedBigInteger('views_total')->default(0);
            $table->unsignedBigInteger('views_daily')->default(0);
            $table->string('payload_hash')->nullable()->unique(); // Idempotency

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
