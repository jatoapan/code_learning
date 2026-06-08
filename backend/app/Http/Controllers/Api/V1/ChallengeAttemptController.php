<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Services\Judge0Service;
use Illuminate\Http\Request;
use App\Enums\ChallengeAttemptStatus;

class ChallengeAttemptController extends Controller
{
    protected $judge0;

    public function __construct(Judge0Service $judge0)
    {
        $this->judge0 = $judge0;
    }

    public function index($id)
    {
        $attempts = ChallengeAttempt::where('challenge_id', $id)->with('user:id,name')->get();
        return response()->json(['data' => $attempts]);
    }

    public function submit(Request $request, $challengeId)
    {
        $validated = $request->validate([
            'submitted_code' => 'required|string',
            'language_id' => 'required|integer',
        ]);

        $challenge = Challenge::with('testCases')->findOrFail($challengeId);

        if ($challenge->testCases->isEmpty()) {
            return response()->json(['message' => 'This challenge has no test cases.'], 400);
        }

        $passed = 0;
        $total = $challenge->testCases->count();
        $status = ChallengeAttemptStatus::Accepted->value;
        $stdout = '';
        $stderr = '';
        $execTime = 0.0;
        $execMemory = 0;

        foreach ($challenge->testCases as $testCase) {
            $result = $this->judge0->submitCode(
                $validated['language_id'],
                $validated['submitted_code'],
                $testCase->expected_output,
                $testCase->input
            );

            if (isset($result['error'])) {
                $status = ChallengeAttemptStatus::CompileError->value;
                $stderr = $result['error'];
                break;
            }

            $judgeStatus = $result['status']['id'] ?? 0;
            
            $execTime += (float) ($result['time'] ?? 0);
            $execMemory += (int) ($result['memory'] ?? 0);

            if ($judgeStatus === 3) {
                $passed++;
            } elseif ($judgeStatus === 4) {
                $status = ChallengeAttemptStatus::WrongAnswer->value;
            } elseif ($judgeStatus === 5) {
                $status = ChallengeAttemptStatus::TimeLimitExceeded->value;
            } elseif ($judgeStatus === 6) {
                $status = ChallengeAttemptStatus::CompileError->value;
                $stderr = $result['compile_output'] ?? '';
                break;
            } else {
                $status = ChallengeAttemptStatus::RuntimeError->value;
                $stderr = $result['stderr'] ?? 'Runtime Error';
                break;
            }

            $stdout .= ($result['stdout'] ?? '') . "\n";
        }

        if ($passed < $total && $status === ChallengeAttemptStatus::Accepted->value) {
            $status = ChallengeAttemptStatus::WrongAnswer->value;
        }

        $points = ($status === ChallengeAttemptStatus::Accepted->value) ? $challenge->points : 0;

        $attempt = new ChallengeAttempt();
        $attempt->user_id = $request->user()->id;
        $attempt->challenge_id = $challenge->id;
        $attempt->submitted_code = $validated['submitted_code'];
        $attempt->language_id = $validated['language_id'];
        $attempt->status = $status;
        $attempt->test_cases_passed = $passed;
        $attempt->test_cases_total = $total;
        $attempt->points_awarded = $points;
        $attempt->execution_time_ms = $execTime * 1000;
        $attempt->execution_memory_kb = $execMemory;
        $attempt->stdout = $stdout;
        $attempt->stderr = $stderr;
        $attempt->save();

        return response()->json([
            'message' => 'Code evaluated',
            'data' => $attempt
        ], 201);
    }

    public function feedback(Request $request, $id)
    {
        $validated = $request->validate([
            'feedback' => 'required|string',
        ]);

        $attempt = ChallengeAttempt::findOrFail($id);
        $attempt->feedback = $validated['feedback'];
        $attempt->save();

        return response()->json(['message' => 'Feedback saved', 'data' => $attempt]);
    }
}
