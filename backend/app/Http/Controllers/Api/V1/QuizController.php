<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\GamificationService;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Requests\SubmitQuizAttemptRequest;
use App\Http\Requests\GeneratePracticeQuizRequest;
use Illuminate\Support\Facades\Gate;

class QuizController extends Controller
{
    public function __construct(private GamificationService $gamificationService) {}

    private function getCourseFromQuiz(Quiz $quiz) {
        $item = $quiz->moduleItems()->with('module.course')->first();
        return $item ? $item->module->course : null;
    }

    public function store(StoreQuizRequest $request, $moduleId) {
        $module = Module::findOrFail($moduleId);
        Gate::authorize('update', $module->course);
        return response()->json(['data' => $this->gamificationService->createQuiz($module, $request->validated())], 201);
    }

    public function show($id) {
        $quiz = $this->gamificationService->getQuizWithDetails($id);
        $course = $this->getCourseFromQuiz($quiz);
        if ($course) Gate::authorize('view', $course);
        return response()->json(['data' => $quiz]);
    }

    public function update(UpdateQuizRequest $request, $id) {
        $quiz = Quiz::findOrFail($id);
        $course = $this->getCourseFromQuiz($quiz);
        if ($course) Gate::authorize('update', $course);
        return response()->json(['data' => $this->gamificationService->updateQuiz($quiz, $request->validated())]);
    }

    public function destroy($id) {
        $quiz = Quiz::findOrFail($id);
        $course = $this->getCourseFromQuiz($quiz);
        if ($course) Gate::authorize('update', $course);
        $this->gamificationService->deleteQuiz($quiz);
        return response()->json(['message' => 'Quiz deleted']);
    }

    public function submit(SubmitQuizAttemptRequest $request, $id) {
        $quiz = Quiz::findOrFail($id);
        $course = $this->getCourseFromQuiz($quiz);
        if ($course) Gate::authorize('view', $course);
        return response()->json(['data' => $this->gamificationService->submitQuizAttempt($quiz, $request->user(), $request->validated()['answers'])], 201);
    }

    public function showAttempt($id) {
        $attempt = $this->gamificationService->getAttemptWithDetails($id);
        Gate::authorize('view', $attempt);

        return response()->json(['data' => $attempt]);
    }

    public function generatePracticeQuiz(GeneratePracticeQuizRequest $request) {
        $validated = $request->validated();
        $quiz = Quiz::findOrFail($validated['quiz_id']);
        $course = $this->getCourseFromQuiz($quiz);
        if ($course) Gate::authorize('view', $course);
        return response()->json(['data' => $this->gamificationService->generatePracticeQuiz($quiz, $validated['question_count'] ?? 10)]);
    }
}
