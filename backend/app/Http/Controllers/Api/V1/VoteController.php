<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\ForumThread;
use App\Models\ForumPost;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    private function toggleVote(Request $request, $model, $value)
    {
        $userId = $request->user()->id;
        
        $vote = $model->votes()->where('user_id', $userId)->first();

        if ($vote) {
            if ($vote->value == $value) {
                $vote->delete(); // Undo vote
                return response()->json(['message' => 'Vote removed']);
            } else {
                $vote->value = $value;
                $vote->save(); // Change vote
                return response()->json(['message' => 'Vote updated']);
            }
        }

        $model->votes()->create([
            'user_id' => $userId,
            'value' => $value
        ]);

        return response()->json(['message' => 'Vote recorded'], 201);
    }

    public function voteThread(Request $request, $id)
    {
        $validated = $request->validate([
            'value' => 'required|integer|in:-1,1'
        ]);
        
        $thread = ForumThread::findOrFail($id);
        return $this->toggleVote($request, $thread, $validated['value']);
    }

    public function votePost(Request $request, $id)
    {
        $validated = $request->validate([
            'value' => 'required|integer|in:-1,1'
        ]);
        
        $post = ForumPost::findOrFail($id);
        return $this->toggleVote($request, $post, $validated['value']);
    }
}
