<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('difficulty', 50);
            $table->unsignedInteger('language_id');
            $table->string('language_name', 50);
            $table->text('starter_code')->nullable();
            $table->unsignedInteger('points')->default(10);
            $table->string('status', 50);
            $table->text('review_feedback')->nullable();
            $table->foreignUuid('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
