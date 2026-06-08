<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function getLanguages()
    {
        return response()->json(['message' => 'List active supported languages in Judge0']);
    }

    public function indexByModule($moduleId)
    {
        return response()->json(['message' => 'List challenges for module', 'module_id' => $moduleId]);
    }

    public function store(Request $request, $moduleId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'language_id' => 'required|integer',
            'language_name' => 'required|string|max:50',
            'starter_code' => 'nullable|string',
            'points' => 'nullable|integer',
            'status' => 'nullable|string|in:draft,pending_review,approved,rejected'
        ]);

        return response()->json([
            'message' => 'Challenge created successfully',
            'module_id' => $moduleId,
            'data' => $validated
        ], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Show challenge details', 'challenge_id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'difficulty' => 'sometimes|required|string|in:easy,medium,hard',
            'starter_code' => 'nullable|string'
        ]);

        return response()->json([
            'message' => 'Challenge updated successfully',
            'data' => $validated
        ]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Challenge deleted successfully'], 204);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:approved,rejected',
            'review_feedback' => 'required_if:status,rejected|string|nullable'
        ]);

        return response()->json([
            'message' => 'Challenge status updated successfully',
            'data' => $validated
        ]);
    }
}
