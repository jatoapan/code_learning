<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChallengeAttemptController extends Controller
{
    public function index($challengeId)
    {
        return response()->json(['message' => 'List attempts for a challenge', 'challenge_id' => $challengeId]);
    }

    public function store(Request $request, $challengeId)
    {
        $validated = $request->validate([
            'submitted_code' => 'required|string',
            'language_id' => 'required|integer'
        ]);

        return response()->json([
            'message' => 'Challenge attempt submitted successfully',
            'challenge_id' => $challengeId,
            'data' => $validated
        ], 201);
    }

    public function feedback(Request $request, $id)
    {
        $validated = $request->validate([
            'feedback' => 'required|string'
        ]);

        return response()->json([
            'message' => 'Feedback added to challenge attempt successfully',
            'data' => $validated
        ]);
    }
}
