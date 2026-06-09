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
    public function __construct(private GamificationService $gamificationService) {}

    public function store(StoreFlashcardRequest $request, $deckId) {
        $deck = FlashcardDeck::findOrFail($deckId);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403);
        return response()->json(['data' => $this->gamificationService->createFlashcard($deck, $request->validated())], 201);
    }

    public function update(UpdateFlashcardRequest $request, $id) {
        $flashcard = Flashcard::with('deck')->findOrFail($id);
        if ($flashcard->deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403);
        return response()->json(['data' => $this->gamificationService->updateFlashcard($flashcard, $request->validated())]);
    }

    public function destroy($id) {
        $flashcard = Flashcard::with('deck')->findOrFail($id);
        if ($flashcard->deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403);
        $this->gamificationService->deleteFlashcard($flashcard);
        return response()->json(['message' => 'Deleted']);
    }

    public function importFromQuiz(ImportFlashcardsFromQuizRequest $request) {
        $validated = $request->validated();
        $deck = FlashcardDeck::findOrFail($validated['deck_id']);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403);
        $this->gamificationService->importFlashcardsFromQuiz($deck, $validated['quiz_id']);
        return response()->json(['message' => 'Imported successfully']);
    }

    public function due(Request $request, $deckId) {
        $deck = FlashcardDeck::findOrFail($deckId);
        if ($deck->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) abort(403);
        
        $flashcards = Flashcard::where('deck_id', $deck->id)
            ->where(function ($query) {
                $query->whereNull('next_review_at')
                      ->orWhere('next_review_at', '<=', now());
            })
            ->get();
        return response()->json(['data' => $flashcards]);
    }

    public function review(ReviewFlashcardRequest $request, $id) {
        $flashcard = Flashcard::with('deck')->findOrFail($id);
        if ($flashcard->deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403);
        $this->gamificationService->reviewFlashcard($flashcard, $request->validated()['quality']);
        return response()->json(['message' => 'Review recorded']);
    }
}
