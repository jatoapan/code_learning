<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeTestCase;
use App\Services\ChallengeService;
use App\Http\Requests\StoreChallengeTestCaseRequest;
use App\Http\Requests\UpdateChallengeTestCaseRequest;
use Illuminate\Support\Facades\Gate;

class ChallengeTestCaseController extends Controller
{
    public function __construct(private ChallengeService $challengeService) {}

    public function store(StoreChallengeTestCaseRequest $request, $challengeId) {
        $challenge = Challenge::with('module.course')->findOrFail($challengeId);
        Gate::authorize('update', $challenge->module->course);
        return response()->json(['data' => $this->challengeService->createTestCase($challenge, $request->validated())], 201);
    }

    public function update(UpdateChallengeTestCaseRequest $request, $id) {
        $testCase = ChallengeTestCase::with('challenge.module.course')->findOrFail($id);
        Gate::authorize('update', $testCase->challenge->module->course);
        return response()->json(['data' => $this->challengeService->updateTestCase($testCase, $request->validated())]);
    }

    public function destroy($id) {
        $testCase = ChallengeTestCase::with('challenge.module.course')->findOrFail($id);
        Gate::authorize('update', $testCase->challenge->module->course);
        $this->challengeService->deleteTestCase($testCase);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
