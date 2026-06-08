<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_id')->nullable()->constrained('flashcard_decks')->nullOnDelete();
            $table->text('question_text');
            $table->text('answer_text');
            $table->foreignId('source_question_id')->nullable()->constrained('quiz_questions')->nullOnDelete();
            $table->timestamp('next_review_at')->useCurrent();
            $table->unsignedInteger('interval')->default(0);
            $table->unsignedInteger('repetitions')->default(0);
            $table->decimal('ease_factor', 5, 2)->default(2.50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
