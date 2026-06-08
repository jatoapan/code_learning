<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('forumable_type');
            $table->string('forumable_id', 36);
            $table->string('title');
            $table->text('body');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50);
            $table->boolean('is_pinned')->default(false);
            $table->integer('vote_score')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('moderator_endorsed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['forumable_type', 'forumable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_threads');
    }
};
