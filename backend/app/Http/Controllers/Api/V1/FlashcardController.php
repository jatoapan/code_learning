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
            'front_content' => 'required|string',
            'back_content' => 'required|string',
        ]);

        $deck = FlashcardDeck::findOrFail($deckId);

        $flashcard = new Flashcard();
        $flashcard->deck_id = $deck->id;
        $flashcard->front_content = $validated['front_content'];
        $flashcard->back_content = $validated['back_content'];
        $flashcard->save();

        return response()->json(['message' => 'Flashcard added successfully', 'data' => $flashcard], 201);
    }
}
