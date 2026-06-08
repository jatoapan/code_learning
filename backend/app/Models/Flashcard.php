<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'deck_id',
        'question_text',
        'answer_text',
        'source_question_id',
        'next_review_at',
        'interval',
        'repetitions',
        'ease_factor',
    ];

    protected $casts = [
        'next_review_at' => 'datetime',
        'ease_factor' => 'decimal:2',
    ];

    public function deck()
    {
        return $this->belongsTo(FlashcardDeck::class, 'deck_id');
    }

    public function sourceQuestion()
    {
        return $this->belongsTo(QuizQuestion::class, 'source_question_id');
    }
}
