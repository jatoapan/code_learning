<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FlashcardDeck;
use App\Models\Flashcard;
use App\Services\GamificationService;
use App\Http\Requests\StoreFlashcardRequest;
use App\Http\Requests\UpdateFlashcardRequest;
use App\Http\Requests\ImportFlashcardsFromQuizRequest;
use App\Http\Requests\ReviewFlashcardRequest;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function store(StoreFlashcardRequest $request, $deckId)
    {
        $deck = FlashcardDeck::findOrFail($deckId);
        $flashcard = $this->gamificationService->createFlashcard($deck, $request->validated());

        return response()->json(['message' => 'Flashcard added successfully', 'data' => $flashcard], 201);
    }

    public function update(UpdateFlashcardRequest $request, $id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $flashcard = $this->gamificationService->updateFlashcard($flashcard, $request->validated());

        return response()->json(['message' => 'Flashcard updated successfully', 'data' => $flashcard]);
    }

    public function destroy($id)
    {
        $flashcard = Flashcard::findOrFail($id);
        if ($flashcard->deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) { abort(403, 'Unauthorized'); }
        
        $this->gamificationService->deleteFlashcard($flashcard);
        return response()->json(['message' => 'Flashcard deleted successfully']);
    }

    public function importFromQuiz(ImportFlashcardsFromQuizRequest $request)
    {
        $validated = $request->validated();
        $deck = FlashcardDeck::findOrFail($validated['deck_id']);
        $this->gamificationService->importFlashcardsFromQuiz($deck, $validated['quiz_id']);

        return response()->json(['message' => 'Imported successfully']);
    }

    public function due(Request $request, $deckId)
    {
        $deck = FlashcardDeck::findOrFail($deckId);
        if ($deck->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) { abort(403, 'Unauthorized'); }
        
        $flashcards = Flashcard::where('deck_id', $deck->id)
            ->where(function ($query) {
                $query->whereNull('next_review_at')
                      ->orWhere('next_review_at', '<=', now());
            })
            ->get();
            
        return response()->json(['data' => $flashcards]);
    }

    public function review(ReviewFlashcardRequest $request, $id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $validated = $request->validated();
        $this->gamificationService->reviewFlashcard($flashcard, $validated['quality']);

        return response()->json(['message' => 'Review recorded successfully']);
    }
}
