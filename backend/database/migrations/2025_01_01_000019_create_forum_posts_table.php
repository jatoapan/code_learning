<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('forum_posts')->nullOnDelete();
            $table->text('body');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_accepted_answer')->default(false);
            $table->integer('vote_score')->default(0);
            $table->string('status', 50);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
    }
};
