<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\FlashcardDeck;
use App\Services\GamificationService;
use App\Http\Requests\StoreFlashcardDeckRequest;
use App\Http\Requests\UpdateFlashcardDeckRequest;
use Illuminate\Support\Facades\Gate;

class FlashcardDeckController extends Controller
{
    public function __construct(private GamificationService $gamificationService) {}

    public function index() {
        return response()->json(['data' => FlashcardDeck::where('user_id', auth()->id())->get()]);
    }

    public function store(StoreFlashcardDeckRequest $request) {
        $validated = $request->validated();
        $module = Module::with('course')->findOrFail($validated['module_id']);
        Gate::authorize('view', $module->course);
        return response()->json(['message' => 'Deck created', 'data' => $this->gamificationService->createFlashcardDeck($module, $request->user(), $validated)], 201);
    }

    public function show($id) {
        $deck = FlashcardDeck::findOrFail($id);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403, 'Unauthorized');
        return response()->json(['data' => $deck]);
    }

    public function update(UpdateFlashcardDeckRequest $request, $id) {
        $deck = FlashcardDeck::findOrFail($id);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403, 'Unauthorized');
        return response()->json(['message' => 'Updated', 'data' => $this->gamificationService->updateFlashcardDeck($deck, $request->validated())]);
    }

    public function destroy($id) {
        $deck = FlashcardDeck::findOrFail($id);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) abort(403, 'Unauthorized');
        $this->gamificationService->deleteFlashcardDeck($deck);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
