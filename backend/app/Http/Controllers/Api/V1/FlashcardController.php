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
}
