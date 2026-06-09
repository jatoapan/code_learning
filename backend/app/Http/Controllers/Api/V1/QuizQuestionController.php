<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\GamificationService;
use App\Http\Requests\StoreQuizQuestionRequest;
use App\Http\Requests\UpdateQuizQuestionRequest;
use App\Http\Requests\UpdateQuizAnswersRequest;
use Illuminate\Support\Facades\Gate;

class QuizQuestionController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function store(StoreQuizQuestionRequest $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $question = $this->gamificationService->createQuizQuestion($quiz, $request->validated());

        return response()->json(['message' => 'Question added successfully', 'data' => $question], 201);
    }

    public function update(UpdateQuizQuestionRequest $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question = $this->gamificationService->updateQuizQuestion($question, $request->validated());
        
        return response()->json(['message' => 'Question updated successfully', 'data' => $question]);
    }

    public function destroy($id)
    {
        $question = QuizQuestion::findOrFail($id);
        
        $moduleItem = $question->quiz->moduleItems()->with('module.course')->first();
        if ($moduleItem) {
            Gate::authorize('update', $moduleItem->module->course);
        }

        $this->gamificationService->deleteQuizQuestion($question);
        
        return response()->json(['message' => 'Question deleted successfully']);
    }

    public function updateAnswers(UpdateQuizAnswersRequest $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);
        $validated = $request->validated();
        $this->gamificationService->updateQuizAnswers($question, $validated['answers']);

        return response()->json(['message' => 'Answers updated successfully']);
    }
}
