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
        $quiz->time_limit = $validated['time_limit_minutes'] ?? null;
        $quiz->passing_score = $validated['passing_score'];
        $quiz->status = QuizStatus::Draft->value;
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

    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'mode' => 'sometimes|required|string|in:practice,exam',
            'time_limit_minutes' => 'nullable|integer',
            'passing_score' => 'sometimes|required|integer|min:0|max:100',
        ]);

        if (isset($validated['time_limit_minutes'])) {
            $validated['time_limit'] = $validated['time_limit_minutes'];
            unset($validated['time_limit_minutes']);
        }

        $quiz->update($validated);
        return response()->json(['message' => 'Quiz updated successfully', 'data' => $quiz]);
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();
        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    public function submit(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $validated = $request->validate([
            'answers' => 'required|array'
        ]);

        $attempt = \App\Models\QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user()->id,
            'score' => 0,
            'passed' => false,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        return response()->json(['message' => 'Attempt submitted', 'data' => $attempt], 201);
    }

    public function showAttempt($id)
    {
        $attempt = \App\Models\QuizAttempt::findOrFail($id);
        return response()->json(['data' => $attempt]);
    }
}
