<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuizQuestionController extends Controller
{
    public function store(Request $request, $quizId)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|string|in:multiple_choice,true_false',
            'points' => 'required|integer|min:1',
            'options' => 'required|array', 
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
        ]);

        $quiz = Quiz::findOrFail($quizId);

        $question = new QuizQuestion();
        $question->quiz_id = $quiz->id;
        $question->question_text = $validated['question_text'];
        $question->type = $validated['type'];
        $question->points = $validated['points'];
        $question->options = $validated['options'];
        $question->correct_answer = $validated['correct_answer'];
        $question->explanation = $validated['explanation'] ?? null;
        $question->save();

        return response()->json(['message' => 'Question added successfully', 'data' => $question], 201);
    }
}
