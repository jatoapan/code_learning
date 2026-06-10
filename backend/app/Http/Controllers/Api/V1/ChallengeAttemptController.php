<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\ChallengeService;
use App\Http\Requests\SubmitChallengeAttemptRequest;
use App\Http\Requests\FeedbackChallengeAttemptRequest;

class ChallengeAttemptController extends Controller
{
    public function __construct(private ChallengeService $challengeService) {}

    public function index(Request $request, $id) {
        $challenge = Challenge::with('module.course')->findOrFail($id);
        Gate::authorize('view', $challenge->module->course); // Basta con poder VER el curso

        $query = ChallengeAttempt::where('challenge_id', $id)->with('user:id,name');

        // Si NO es staff (no puede actualizar el curso), solo ve sus propios intentos
        if (!\Illuminate\Support\Facades\Gate::allows('update', $challenge->module->course)) {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function submit(SubmitChallengeAttemptRequest $request, $challengeId) {
        $challenge = Challenge::with(['testCases', 'module.course'])->findOrFail($challengeId);
        Gate::authorize('view', $challenge->module->course);

        try {
            $attempt = $this->challengeService->submitAttempt($challenge, $request->validated(), $request->user()->id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => 'Code evaluated', 'data' => $attempt], 201);
    }

    public function feedback(FeedbackChallengeAttemptRequest $request, $id) {
        $attempt = ChallengeAttempt::with('challenge.module.course')->findOrFail($id);
        Gate::authorize('update', $attempt->challenge->module->course);
        
        return response()->json([
            'message' => 'Feedback saved', 
            'data' => $this->challengeService->updateAttemptFeedback($attempt, $request->validated())
        ]);
    }
}
