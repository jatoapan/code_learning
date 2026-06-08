<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Challenge;
use App\Models\ModuleItem;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function indexByModule($moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $challenges = ModuleItem::where('module_id', $module->id)
                                ->where('itemable_type', Challenge::class)
                                ->with('itemable')
                                ->get()
                                ->pluck('itemable');
                                
        return response()->json(['data' => $challenges]);
    }

    public function show($id)
    {
        $challenge = Challenge::with('testCases')->findOrFail($id);
        return response()->json(['data' => $challenge]);
    }

    public function store(Request $request, $moduleId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'language_id' => 'required|integer',
            'language_name' => 'required|string',
            'points' => 'required|integer|min:0',
            'starter_code' => 'nullable|string',
        ]);

        $module = Module::findOrFail($moduleId);

        $challenge = new Challenge();
        $challenge->module_id = $module->id;
        $challenge->title = $validated['title'];
        $challenge->description = $validated['description'];
        $challenge->difficulty = $validated['difficulty'];
        $challenge->language_id = $validated['language_id'];
        $challenge->language_name = $validated['language_name'];
        $challenge->starter_code = $validated['starter_code'] ?? null;
        $challenge->points = $validated['points'];
        $challenge->creator_id = $request->user()->id;
        $challenge->status = 'draft';
        $challenge->save();

        ModuleItem::create([
            'module_id' => $module->id,
            'itemable_type' => Challenge::class,
            'itemable_id' => $challenge->id,
            'order' => 1
        ]);

        return response()->json(['message' => 'Challenge created successfully', 'data' => $challenge], 201);
    }

    public function update(Request $request, $id)
    {
        $challenge = Challenge::findOrFail($id);
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'difficulty' => 'string|in:easy,medium,hard',
            'points' => 'integer|min:0',
        ]);
        $challenge->update($validated);
        return response()->json(['message' => 'Updated successfully', 'data' => $challenge]);
    }

    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);
        $challenge->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function languages()
    {
        // IDs típicos de Judge0 CE: 71 = Python 3, 62 = Java, 54 = C++
        return response()->json([
            'data' => [
                ['id' => 71, 'name' => 'Python (3.8.1)'],
                ['id' => 62, 'name' => 'Java (OpenJDK 13.0.1)'],
                ['id' => 54, 'name' => 'C++ (GCC 9.2.0)'],
            ]
        ]);
    }
}
