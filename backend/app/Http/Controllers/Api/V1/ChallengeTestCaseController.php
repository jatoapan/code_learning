<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChallengeTestCaseController extends Controller
{
    public function store(Request $request, $challengeId)
    {
        $validated = $request->validate([
            'input' => 'nullable|string',
            'expected_output' => 'required|string',
            'is_hidden' => 'boolean'
        ]);

        return response()->json([
            'message' => 'Challenge test case created successfully',
            'challenge_id' => $challengeId,
            'data' => $validated
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'input' => 'nullable|string',
            'expected_output' => 'sometimes|required|string',
            'is_hidden' => 'boolean'
        ]);

        return response()->json([
            'message' => 'Challenge test case updated successfully',
            'data' => $validated
        ]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Challenge test case deleted successfully'], 204);
    }
}
