<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('time_limit')->nullable();
            $table->unsignedInteger('max_attempts')->nullable();
            $table->decimal('passing_score', 5, 2)->default(60.00);
            $table->unsignedInteger('random_question_limit')->nullable();
            $table->string('status', 50)->default('draft');
            $table->string('mode', 50)->default('practice');
            $table->timestamp('answers_visible_after')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
