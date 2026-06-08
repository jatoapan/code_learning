<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category', 50);
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('status', 50)->default('draft');
            $table->boolean('has_leaderboard')->default(true);
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['slug', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
