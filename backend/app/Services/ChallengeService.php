<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Challenge;
use App\Models\ModuleItem;
use App\Models\ChallengeTestCase;
use App\Models\ChallengeAttempt;
use App\Enums\ChallengeAttemptStatus;
use Illuminate\Support\Facades\DB;
use App\Services\Judge0Service;
use InvalidArgumentException;

class ChallengeService
{
    protected $judge0;

    public function __construct(Judge0Service $judge0)
    {
        $this->judge0 = $judge0;
    }

    public function createChallenge(array $data, Module $module, string $creatorId)
    {
        return DB::transaction(function () use ($data, $module, $creatorId) {
            $challenge = new Challenge();
            $challenge->module_id = $module->id;
            $challenge->title = $data['title'];
            $challenge->description = $data['description'];
            $challenge->difficulty = $data['difficulty'];
            $challenge->language_id = $data['language_id'];
            $challenge->language_name = $data['language_name'];
            $challenge->starter_code = $data['starter_code'] ?? null;
            $challenge->points = $data['points'];
            $challenge->creator_id = $creatorId;
            $challenge->status = 'draft';
            $challenge->save();

            ModuleItem::create([
                'module_id' => $module->id,
                'itemable_type' => Challenge::class,
                'itemable_id' => $challenge->id,
                'order' => 1
            ]);

            return $challenge;
        });
    }

    public function updateChallenge(Challenge $challenge, array $data)
    {
        $challenge->update($data);
        return $challenge;
    }

    public function deleteChallenge(Challenge $challenge)
    {
        $challenge->delete();
    }

    public function createTestCase(Challenge $challenge, array $data)
    {
        $testCase = new ChallengeTestCase();
        $testCase->challenge_id = $challenge->id;
        $testCase->input = $data['input'] ?? null;
        $testCase->expected_output = $data['expected_output'];
        $testCase->is_hidden = $data['is_hidden'] ?? false;
        $testCase->save();

        return $testCase;
    }

    public function updateTestCase(ChallengeTestCase $testCase, array $data)
    {
        $testCase->input = $data['input'] ?? $testCase->input;
        $testCase->expected_output = $data['expected_output'] ?? $testCase->expected_output;
        $testCase->is_hidden = $data['is_hidden'] ?? $testCase->is_hidden;
        $testCase->save();

        return $testCase;
    }

    public function deleteTestCase(ChallengeTestCase $testCase)
    {
        $testCase->delete();
    }

    public function submitAttempt(Challenge $challenge, array $data, string $userId)
    {
        if ($challenge->testCases->isEmpty()) {
            throw new InvalidArgumentException('This challenge has no test cases.');
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
                $data['language_id'],
                $data['submitted_code'],
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
        $attempt->user_id = $userId;
        $attempt->challenge_id = $challenge->id;
        $attempt->submitted_code = $data['submitted_code'];
        $attempt->language_id = $data['language_id'];
        $attempt->status = $status;
        $attempt->test_cases_passed = $passed;
        $attempt->test_cases_total = $total;
        $attempt->points_awarded = $points;
        $attempt->execution_time_ms = $execTime * 1000;
        $attempt->execution_memory_kb = $execMemory;
        $attempt->stdout = $stdout;
        $attempt->stderr = $stderr;
        $attempt->save();

        return $attempt;
    }

    public function updateAttemptFeedback(ChallengeAttempt $attempt, array $data)
    {
        $attempt->feedback = $data['feedback'];
        $attempt->save();

        return $attempt;
    }
}
