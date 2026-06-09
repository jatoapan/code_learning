<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\FlashcardDeck;
use App\Models\ModuleItem;
use Illuminate\Http\Request;

class FlashcardDeckController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module = Module::findOrFail($validated['module_id']);

        $deck = new FlashcardDeck();
        $deck->title = $validated['title'];
        $deck->description = $validated['description'] ?? null;
        $deck->user_id = $request->user()->id;
        $deck->save();

        ModuleItem::create([
            'module_id' => $module->id,
            'itemable_type' => FlashcardDeck::class,
            'itemable_id' => $deck->id,
            'order' => 1
        ]);

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

    public function update(Request $request, $id)
    {
        $deck = FlashcardDeck::findOrFail($id);
        if ($deck->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) { abort(403, 'Unauthorized'); }
        
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
        ]);
        $deck->update($validated);
        return response()->json(['message' => 'Updated successfully', 'data' => $deck]);
    }

    public function destroy($id)
    {
        $deck = FlashcardDeck::findOrFail($id);
        if ($deck->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) { abort(403, 'Unauthorized'); }
        
        $deck->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
