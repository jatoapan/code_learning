<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use Illuminate\Http\Request;
use App\Services\ChallengeService;
use App\Http\Requests\SubmitChallengeAttemptRequest;
use App\Http\Requests\FeedbackChallengeAttemptRequest;

class ChallengeAttemptController extends Controller
{
    protected $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
    }

    public function index($id)
    {
        $attempts = ChallengeAttempt::where('challenge_id', $id)->with('user:id,name')->get();
        return response()->json(['data' => $attempts]);
    }

    public function submit(SubmitChallengeAttemptRequest $request, $challengeId)
    {
        $challenge = Challenge::with('testCases')->findOrFail($challengeId);

        try {
            $attempt = $this->challengeService->submitAttempt(
                $challenge, 
                $request->validated(), 
                $request->user()->id
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json([
            'message' => 'Code evaluated',
            'data' => $attempt
        ], 201);
    }

    public function feedback(FeedbackChallengeAttemptRequest $request, $id)
    {
        $attempt = ChallengeAttempt::findOrFail($id);
        
        $attempt = $this->challengeService->updateAttemptFeedback($attempt, $request->validated());

        return response()->json(['message' => 'Feedback saved', 'data' => $attempt]);
    }
}
