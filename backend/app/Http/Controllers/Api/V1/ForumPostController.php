<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForumPostController extends Controller
{
    public function store(Request $request, $threadId)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'parent_id' => 'nullable|uuid',
        ]);

        return response()->json([
            'message' => 'Post created successfully',
            'data' => $validated
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        return response()->json([
            'message' => 'Post updated successfully',
            'data' => $validated
        ], 200);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Post deleted successfully'
        ], 200);
    }

    public function markAcceptedAnswer(Request $request, $id)
    {
        $validated = $request->validate([
            'is_accepted_answer' => 'required|boolean',
        ]);

        return response()->json([
            'message' => 'Post accepted answer status updated',
            'data' => $validated
        ], 200);
    }
}
