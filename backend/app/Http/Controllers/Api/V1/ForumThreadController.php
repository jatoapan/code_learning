<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use Illuminate\Http\Request;
use App\Enums\ThreadStatus;

class ForumThreadController extends Controller
{
    public function indexByCourse($id)
    {
        $threads = ForumThread::where('forumable_type', Course::class)
                              ->where('forumable_id', $id)
                              ->with('user:id,name,avatar_path')
                              ->paginate(15);
        return response()->json(['data' => $threads]);
    }

    public function indexByModule($id)
    {
        $threads = ForumThread::where('forumable_type', Module::class)
                              ->where('forumable_id', $id)
                              ->with('user:id,name,avatar_path')
                              ->paginate(15);
        return response()->json(['data' => $threads]);
    }

    public function indexByChallenge($id)
    {
        $threads = ForumThread::where('forumable_type', Challenge::class)
                              ->where('forumable_id', $id)
                              ->with('user:id,name,avatar_path')
                              ->paginate(15);
        return response()->json(['data' => $threads]);
    }

    private function storeThread(Request $request, $forumableType, $forumableId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $thread = new ForumThread();
        $thread->title = $validated['title'];
        $thread->body = $validated['body'];
        $thread->user_id = $request->user()->id;
        $thread->forumable_type = $forumableType;
        $thread->forumable_id = $forumableId;
        $thread->status = ThreadStatus::Open->value;
        $thread->save();

        return response()->json(['message' => 'Thread created successfully', 'data' => $thread], 201);
    }

    public function storeCourseThread(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        return $this->storeThread($request, Course::class, $course->id);
    }

    public function storeModuleThread(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        return $this->storeThread($request, Module::class, $module->id);
    }

    public function storeChallengeThread(Request $request, $id)
    {
        $challenge = Challenge::findOrFail($id);
        return $this->storeThread($request, Challenge::class, $challenge->id);
    }

    public function show($id)
    {
        $thread = ForumThread::with(['user:id,name', 'posts.user:id,name'])->findOrFail($id);
        return response()->json(['data' => $thread]);
    }

    public function update(Request $request, $id)
    {
        $thread = ForumThread::findOrFail($id);
        
        if ($thread->user_id !== $request->user()->id && !$request->user()->hasRole('admin|moderator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        $thread->update($validated);

        return response()->json(['message' => 'Thread updated', 'data' => $thread]);
    }

    public function destroy(Request $request, $id)
    {
        $thread = ForumThread::findOrFail($id);
        if ($thread->user_id !== $request->user()->id && !$request->user()->hasRole('admin|moderator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $thread->delete();
        return response()->json(['message' => 'Thread deleted']);
    }

    public function togglePin(Request $request, $id)
    {
        $thread = ForumThread::findOrFail($id);
        if (!$request->user()->hasRole('admin|moderator|professor|ta')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $thread->is_pinned = !$thread->is_pinned;
        $thread->save();
        return response()->json(['message' => 'Thread pin toggled', 'data' => $thread]);
    }

    public function lock(Request $request, $id)
    {
        $thread = ForumThread::findOrFail($id);
        if (!$request->user()->hasRole('admin|moderator|professor|ta')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $thread->status = ThreadStatus::Locked->value;
        $thread->save();
        return response()->json(['message' => 'Thread locked', 'data' => $thread]);
    }
}
