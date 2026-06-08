<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\ModuleItem;
use Illuminate\Http\Request;
use App\Enums\QuizStatus;

class QuizController extends Controller
{
    public function store(Request $request, $moduleId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mode' => 'required|string|in:practice,exam',
            'time_limit_minutes' => 'nullable|integer',
            'passing_score' => 'required|integer|min:0|max:100',
        ]);

        $module = Module::findOrFail($moduleId);

        $quiz = new Quiz();
        $quiz->title = $validated['title'];
        $quiz->description = $validated['description'] ?? null;
        $quiz->mode = $validated['mode'];
        $quiz->time_limit_minutes = $validated['time_limit_minutes'] ?? null;
        $quiz->passing_score = $validated['passing_score'];
        $quiz->status = QuizStatus::Draft->value;
        $quiz->creator_id = $request->user()->id;
        $quiz->save();

        ModuleItem::create([
            'module_id' => $module->id,
            'itemable_type' => Quiz::class,
            'itemable_id' => $quiz->id,
            'order' => 1
        ]);

        return response()->json(['message' => 'Quiz created successfully', 'data' => $quiz], 201);
    }

    public function show($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        return response()->json(['data' => $quiz]);
    }
}
