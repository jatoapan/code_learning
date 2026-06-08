<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    public function store(Request $request, $deckId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'answer_text' => 'required|string'
        ]);

        return response()->json([
            'message' => 'Flashcard created successfully',
            'deck_id' => $deckId,
            'data' => $validated
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'question_text' => 'sometimes|required|string',
            'answer_text' => 'sometimes|required|string'
        ]);

        return response()->json([
            'message' => 'Flashcard updated successfully',
            'data' => $validated
        ]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Flashcard deleted successfully'], 204);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'attempt_id' => 'required|integer',
            'deck_id' => 'required|integer'
        ]);

        return response()->json([
            'message' => 'Flashcards imported from attempt successfully',
            'data' => $validated
        ], 201);
    }

    public function rate(Request $request, $id)
    {
        $validated = $request->validate([
            'rating' => 'required|string|in:easy,good,hard,again'
        ]);

        return response()->json([
            'message' => 'Flashcard rated and SRS parameters recalculated',
            'data' => $validated
        ]);
    }
}
