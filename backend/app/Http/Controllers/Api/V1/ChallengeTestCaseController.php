<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeTestCase;
use Illuminate\Http\Request;
use App\Services\ChallengeService;
use App\Http\Requests\StoreChallengeTestCaseRequest;
use App\Http\Requests\UpdateChallengeTestCaseRequest;

class ChallengeTestCaseController extends Controller
{
    protected $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
    }

    public function store(StoreChallengeTestCaseRequest $request, $challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);

        $testCase = $this->challengeService->createTestCase($challenge, $request->validated());

        return response()->json(['message' => 'Test case added successfully', 'data' => $testCase], 201);
    }

    public function update(UpdateChallengeTestCaseRequest $request, $id)
    {
        $testCase = ChallengeTestCase::findOrFail($id);

        $testCase = $this->challengeService->updateTestCase($testCase, $request->validated());

        return response()->json(['message' => 'Test case updated successfully', 'data' => $testCase]);
    }

    public function destroy($id)
    {
        $testCase = ChallengeTestCase::findOrFail($id);
        
        $this->challengeService->deleteTestCase($testCase);

        return response()->json(['message' => 'Test case deleted successfully']);
    }
}
