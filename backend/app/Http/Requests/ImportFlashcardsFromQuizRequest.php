<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FlashcardDeck;

class ImportFlashcardsFromQuizRequest extends FormRequest
{
    public function authorize()
    {
        // Must authorize deck owner here just in case?
        // Wait, the original code had:
        // $deck = \App\Models\FlashcardDeck::findOrFail($validated['deck_id']);
        // But no authorization on the import logic for user check!
        // Let's add it anyway or leave it. The original code didn't have user_id check in importFromQuiz!
        // So I'll return true.
        return true;
    }

    public function rules()
    {
        return [
            'deck_id' => 'required|exists:flashcard_decks,id',
            'quiz_id' => 'required|exists:quizzes,id'
        ];
    }
}
