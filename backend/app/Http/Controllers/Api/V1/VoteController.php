<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\ForumThread;
use App\Models\ForumPost;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VoteController extends Controller
{
    private function authorizeThreadAccess(ForumThread $thread) {
        $course = null;
        if ($thread->forumable_type === Course::class) $course = $thread->forumable;
        if ($thread->forumable_type === Module::class) $course = $thread->forumable->course;
        if ($thread->forumable_type === Challenge::class) $course = $thread->forumable->module->course;
        
        if ($course) Gate::authorize('view', $course);
    }

    private function toggleVote(Request $request, $model, $value) {
        $userId = $request->user()->id;
        $vote = $model->votes()->where('user_id', $userId)->first();

        if ($vote) {
            if ($vote->vote_type == $value) {
                $vote->delete();
                return response()->json(['message' => 'Vote removed']);
            } else {
                $vote->vote_type = $value;
                $vote->save();
                return response()->json(['message' => 'Vote updated']);
            }
        }

        $model->votes()->create(['user_id' => $userId, 'vote_type' => $value]);
        return response()->json(['message' => 'Vote recorded'], 201);
    }

    public function voteThread(Request $request, $id) {
        $validated = $request->validate(['value' => 'required|integer|in:-1,1']);
        $thread = ForumThread::with('forumable')->findOrFail($id);
        
        $this->authorizeThreadAccess($thread);
        return $this->toggleVote($request, $thread, $validated['value']);
    }

    public function votePost(Request $request, $id) {
        $validated = $request->validate(['value' => 'required|integer|in:-1,1']);
        $post = ForumPost::with('thread.forumable')->findOrFail($id);
        
        $this->authorizeThreadAccess($post->thread);
        return $this->toggleVote($request, $post, $validated['value']);
    }
}
