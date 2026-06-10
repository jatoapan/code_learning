<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Challenge;
use App\Models\ModuleItem;
use App\Models\ChallengeTestCase;
use App\Models\ChallengeAttempt;
use App\Enums\ChallengeAttemptStatus;
use App\Jobs\ProcessChallengeAttempt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChallengeService
{

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

    /**
     * Crea un attempt en estado 'pending' y despacha el Job al Queue Worker.
     * El resultado llegará al Frontend vía Reverb WebSocket cuando termine.
     */
    public function submitAttempt(Challenge $challenge, array $data, string $userId): ChallengeAttempt
    {
        if ($challenge->testCases->isEmpty()) {
            throw new InvalidArgumentException('This challenge has no test cases.');
        }

        // Crear attempt inmediatamente con status pending
        $attempt = new ChallengeAttempt();
        $attempt->user_id        = $userId;
        $attempt->challenge_id   = $challenge->id;
        $attempt->submitted_code = $data['submitted_code'];
        $attempt->language_id    = $data['language_id'];
        $attempt->status         = ChallengeAttemptStatus::Pending->value;
        $attempt->test_cases_passed = 0;
        $attempt->test_cases_total  = $challenge->testCases->count();
        $attempt->points_awarded    = 0;
        $attempt->save();

        // Despachar Job al Queue Worker para evaluación asíncrona
        ProcessChallengeAttempt::dispatch($attempt, $challenge, $data);

        return $attempt;
    }

    public function updateAttemptFeedback(ChallengeAttempt $attempt, array $data)
    {
        $attempt->feedback = $data['feedback'];
        $attempt->save();

        return $attempt;
    }
}
