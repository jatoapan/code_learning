<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Services\GamificationService;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Requests\SubmitQuizAttemptRequest;
use App\Http\Requests\GeneratePracticeQuizRequest;
use Illuminate\Support\Facades\Gate;

class QuizController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function store(StoreQuizRequest $request, $moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $quiz = $this->gamificationService->createQuiz($module, $request->validated());

        return response()->json(['message' => 'Quiz created successfully', 'data' => $quiz], 201);
    }

    public function show($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        return response()->json(['data' => $quiz]);
    }

    public function update(UpdateQuizRequest $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz = $this->gamificationService->updateQuiz($quiz, $request->validated());

        return response()->json(['message' => 'Quiz updated successfully', 'data' => $quiz]);
    }

    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        
        $moduleItem = $quiz->moduleItems()->with('module.course')->first();
        if ($moduleItem) {
            Gate::authorize('update', $moduleItem->module->course);
        }

        $this->gamificationService->deleteQuiz($quiz);
        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    public function submit(SubmitQuizAttemptRequest $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $validated = $request->validated();
        $attempt = $this->gamificationService->submitQuizAttempt($quiz, $request->user(), $validated['answers']);

        return response()->json(['message' => 'Attempt submitted', 'data' => $attempt], 201);
    }

    public function showAttempt($id)
    {
        $attempt = \App\Models\QuizAttempt::findOrFail($id);
        return response()->json(['data' => $attempt]);
    }

    public function generatePracticeQuiz(GeneratePracticeQuizRequest $request)
    {
        $validated = $request->validated();
        $quiz = Quiz::findOrFail($validated['quiz_id']);
        $count = $validated['question_count'] ?? 10;
        
        $questions = $this->gamificationService->generatePracticeQuiz($quiz, $count);

        return response()->json(['data' => $questions]);
    }
}
