<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FlashcardDeck;
use App\Models\Flashcard;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    public function store(Request $request, $deckId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'answer_text' => 'required|string',
        ]);

        $deck = FlashcardDeck::findOrFail($deckId);

        $flashcard = new Flashcard();
        $flashcard->deck_id = $deck->id;
        $flashcard->question_text = $validated['question_text'];
        $flashcard->answer_text = $validated['answer_text'];
        $flashcard->save();

        return response()->json(['message' => 'Flashcard added successfully', 'data' => $flashcard], 201);
    }

    public function update(Request $request, $id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $validated = $request->validate([
            'question_text' => 'sometimes|required|string',
            'answer_text' => 'sometimes|required|string',
        ]);
        $flashcard->update($validated);
        return response()->json(['message' => 'Flashcard updated successfully', 'data' => $flashcard]);
    }

    public function destroy($id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $flashcard->delete();
        return response()->json(['message' => 'Flashcard deleted successfully']);
    }

    public function importFromQuiz(Request $request, $deckId)
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id'
        ]);

        $deck = \App\Models\FlashcardDeck::findOrFail($deckId);
        $questions = \App\Models\QuizQuestion::where('quiz_id', $validated['quiz_id'])->get();

        foreach ($questions as $question) {
            $correctAnswer = \App\Models\QuizAnswer::where('question_id', $question->id)
                                                   ->where('is_correct', true)
                                                   ->first();
                                                   
            Flashcard::create([
                'deck_id' => $deck->id,
                'question_text' => $question->question_text,
                'answer_text' => $correctAnswer ? $correctAnswer->answer_text : 'No answer provided'
            ]);
        }

        return response()->json(['message' => 'Imported successfully']);
    }

    public function due(Request $request, $deckId)
    {
        $deck = \App\Models\FlashcardDeck::findOrFail($deckId);
        
        $flashcards = Flashcard::where('deck_id', $deck->id)
            ->where(function ($query) {
                $query->whereNull('next_review_at')
                      ->orWhere('next_review_at', '<=', now());
            })
            ->get();
            
        return response()->json(['data' => $flashcards]);
    }

    public function review(Request $request, $id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $validated = $request->validate([
            'quality' => 'required|integer|min:0|max:5',
        ]);
        
        $flashcard->next_review_at = now()->addDays($validated['quality']);
        $flashcard->save();

        return response()->json(['message' => 'Review recorded successfully']);
    }
}
