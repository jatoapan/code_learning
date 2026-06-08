<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeTestCase;
use Illuminate\Http\Request;

class ChallengeTestCaseController extends Controller
{
    public function store(Request $request, $challengeId)
    {
        $validated = $request->validate([
            'input' => 'nullable|string',
            'expected_output' => 'required|string',
            'is_hidden' => 'boolean',
        ]);

        $challenge = Challenge::findOrFail($challengeId);

        $testCase = new ChallengeTestCase();
        $testCase->challenge_id = $challenge->id;
        $testCase->input = $validated['input'] ?? null;
        $testCase->expected_output = $validated['expected_output'];
        $testCase->is_hidden = $validated['is_hidden'] ?? false;
        $testCase->save();

        return response()->json(['message' => 'Test case added successfully', 'data' => $testCase], 201);
    }

    public function update(Request $request, $id)
    {
        $testCase = ChallengeTestCase::findOrFail($id);

        $validated = $request->validate([
            'input' => 'nullable|string',
            'expected_output' => 'required|string',
            'is_hidden' => 'boolean',
        ]);

        $testCase->input = $validated['input'] ?? $testCase->input;
        $testCase->expected_output = $validated['expected_output'] ?? $testCase->expected_output;
        $testCase->is_hidden = $validated['is_hidden'] ?? $testCase->is_hidden;
        $testCase->save();

        return response()->json(['message' => 'Test case updated successfully', 'data' => $testCase]);
    }

    public function destroy($id)
    {
        $testCase = ChallengeTestCase::findOrFail($id);
        $testCase->delete();

        return response()->json(['message' => 'Test case deleted successfully']);
    }
}
