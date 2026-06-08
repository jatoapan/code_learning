<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function store(Request $request, $moduleId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'passing_score' => 'numeric|min:0|max:100',
            'random_question_limit' => 'nullable|integer|min:1',
            'mode' => 'required|string|in:practice,exam',
            'answers_visible_after' => 'nullable|date'
        ]);

        return response()->json([
            'message' => 'Quiz created successfully',
            'module_id' => $moduleId,
            'data' => $validated
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'passing_score' => 'numeric|min:0|max:100',
            'random_question_limit' => 'nullable|integer|min:1',
            'mode' => 'sometimes|required|string|in:practice,exam',
            'answers_visible_after' => 'nullable|date'
        ]);

        return response()->json([
            'message' => 'Quiz updated successfully',
            'data' => $validated
        ]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Quiz deleted successfully'], 204);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show quiz details', 'quiz_id' => $id]);
    }

    public function attempt(Request $request, $id)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer_id' => 'required|integer',
        ]);

        return response()->json([
            'message' => 'Quiz attempt submitted successfully',
            'quiz_id' => $id,
            'data' => $validated
        ], 201);
    }

    public function showAttempt($id)
    {
        return response()->json(['message' => 'Show quiz attempt details', 'attempt_id' => $id]);
    }

    public function generatePracticeQuiz(Request $request)
    {
        $validated = $request->validate([
            'deck_id' => 'required|integer'
        ]);

        return response()->json([
            'message' => 'Practice quiz generated from deck successfully',
            'data' => $validated
        ], 201);
    }
}
