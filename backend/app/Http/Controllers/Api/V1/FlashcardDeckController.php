<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\FlashcardDeck;
use App\Services\GamificationService;
use App\Http\Requests\StoreFlashcardDeckRequest;
use App\Http\Requests\UpdateFlashcardDeckRequest;

class FlashcardDeckController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function store(StoreFlashcardDeckRequest $request)
    {
        $validated = $request->validated();
        $module = Module::findOrFail($validated['module_id']);
        $deck = $this->gamificationService->createFlashcardDeck($module, $request->user(), $validated);

        return response()->json(['message' => 'Deck created successfully', 'data' => $deck], 201);
    }

    public function index()
    {
        return response()->json(['data' => FlashcardDeck::all()]);
    }

    public function show($id)
    {
        $deck = FlashcardDeck::findOrFail($id);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) { abort(403, 'Unauthorized'); }
        return response()->json(['data' => $deck]);
    }

    public function update(UpdateFlashcardDeckRequest $request, $id)
    {
        $deck = FlashcardDeck::findOrFail($id);
        $deck = $this->gamificationService->updateFlashcardDeck($deck, $request->validated());

        return response()->json(['message' => 'Updated successfully', 'data' => $deck]);
    }

    public function destroy($id)
    {
        $deck = FlashcardDeck::findOrFail($id);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) { abort(403, 'Unauthorized'); }
        
        $this->gamificationService->deleteFlashcardDeck($deck);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
