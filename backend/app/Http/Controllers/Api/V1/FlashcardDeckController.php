<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FlashcardDeckController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List flashcard decks']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        return response()->json([
            'message' => 'Flashcard deck created successfully',
            'data' => $validated
        ], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show flashcard deck details', 'deck_id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        return response()->json([
            'message' => 'Flashcard deck updated successfully',
            'data' => $validated
        ]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Flashcard deck deleted successfully'], 204);
    }

    public function dueFlashcards($id)
    {
        return response()->json(['message' => 'List due flashcards for deck', 'deck_id' => $id]);
    }
}
