<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->nullable()->constrained('quiz_attempts')->nullOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('quiz_questions')->nullOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('quiz_answers')->nullOnDelete();
            $table->boolean('is_correct');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
    }
};
