<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function voteThread(Request $request, $id)
    {
        $validated = $request->validate([
            'vote_type' => 'required|integer|in:1,-1',
        ]);

        return response()->json([
            'message' => 'Vote recorded for thread',
            'data' => $validated
        ], 200);
    }

    public function votePost(Request $request, $id)
    {
        $validated = $request->validate([
            'vote_type' => 'required|integer|in:1,-1',
        ]);

        return response()->json([
            'message' => 'Vote recorded for post',
            'data' => $validated
        ], 200);
    }
}
