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
    public function __construct(private GamificationService $gamificationService) {}

    private function getCourseFromQuiz(Quiz $quiz) {
        $item = $quiz->moduleItems()->with('module.course')->first();
        return $item ? $item->module->course : null;
    }

    public function store(StoreQuizQuestionRequest $request, $quizId) {
        $quiz = Quiz::findOrFail($quizId);
        $course = $this->getCourseFromQuiz($quiz);
        if ($course) Gate::authorize('update', $course);
        return response()->json(['data' => $this->gamificationService->createQuizQuestion($quiz, $request->validated())], 201);
    }

    public function update(UpdateQuizQuestionRequest $request, $id) {
        $question = QuizQuestion::with('quiz')->findOrFail($id);
        $course = $this->getCourseFromQuiz($question->quiz);
        if ($course) Gate::authorize('update', $course);
        return response()->json(['data' => $this->gamificationService->updateQuizQuestion($question, $request->validated())]);
    }

    public function destroy($id) {
        $question = QuizQuestion::with('quiz')->findOrFail($id);
        $course = $this->getCourseFromQuiz($question->quiz);
        if ($course) Gate::authorize('update', $course);
        $this->gamificationService->deleteQuizQuestion($question);
        return response()->json(['message' => 'Deleted']);
    }

    public function updateAnswers(UpdateQuizAnswersRequest $request, $id) {
        $question = QuizQuestion::with('quiz')->findOrFail($id);
        $course = $this->getCourseFromQuiz($question->quiz);
        if ($course) Gate::authorize('update', $course);
        $this->gamificationService->updateQuizAnswers($question, $request->validated()['answers']);
        return response()->json(['message' => 'Answers updated']);
    }
}
