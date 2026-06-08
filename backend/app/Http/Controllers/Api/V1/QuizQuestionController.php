<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuizQuestionController extends Controller
{
    public function store(Request $request, $quizId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|string|in:multiple_choice,true_false',
            'points' => 'integer|min:1',
            'explanation' => 'nullable|string',
            'answers' => 'required|array|min:2',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean'
        ]);

        return response()->json([
            'message' => 'Quiz question created successfully',
            'quiz_id' => $quizId,
            'data' => $validated
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'question_text' => 'sometimes|required|string',
            'type' => 'sometimes|required|string|in:multiple_choice,true_false',
            'points' => 'integer|min:1',
            'explanation' => 'nullable|string'
        ]);

        return response()->json([
            'message' => 'Quiz question updated successfully',
            'data' => $validated
        ]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Quiz question deleted successfully'], 204);
    }

    public function updateAnswers(Request $request, $id)
    {
        $validated = $request->validate([
            'answers' => 'required|array|min:2',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean'
        ]);

        return response()->json([
            'message' => 'Quiz question answers updated successfully',
            'data' => $validated
        ]);
    }
}
