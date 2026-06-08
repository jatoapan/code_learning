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
        $question->explanation = $validated['explanation'] ?? null;
        $question->save();

        foreach ($validated['options'] as $option) {
            \App\Models\QuizAnswer::create([
                'question_id' => $question->id,
                'answer_text' => $option,
                'is_correct' => ($option === $validated['correct_answer'])
            ]);
        }

        return response()->json(['message' => 'Question added successfully', 'data' => $question], 201);
    }

    public function update(Request $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);
        $validated = $request->validate([
            'question_text' => 'sometimes|required|string',
            'type' => 'sometimes|required|string|in:multiple_choice,true_false',
            'points' => 'sometimes|required|integer|min:1',
            'explanation' => 'nullable|string',
        ]);
        
        $question->update($validated);
        
        return response()->json(['message' => 'Question updated successfully', 'data' => $question]);
    }

    public function destroy($id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question->delete();
        
        return response()->json(['message' => 'Question deleted successfully']);
    }

    public function updateAnswers(Request $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.id' => 'nullable|exists:quiz_answers,id',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        foreach ($validated['answers'] as $answerData) {
            if (isset($answerData['id'])) {
                $answer = \App\Models\QuizAnswer::where('question_id', $question->id)->findOrFail($answerData['id']);
                $answer->update([
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct']
                ]);
            } else {
                \App\Models\QuizAnswer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct']
                ]);
            }
        }

        return response()->json(['message' => 'Answers updated successfully']);
    }
}
