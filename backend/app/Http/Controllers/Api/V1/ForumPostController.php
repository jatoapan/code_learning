<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumThread;
use Illuminate\Http\Request;

class ForumPostController extends Controller
{
    public function store(Request $request, $threadId)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $thread = ForumThread::findOrFail($threadId);

        $post = new ForumPost();
        $post->body = $validated['body'];
        $post->user_id = $request->user()->id;
        $post->thread_id = $thread->id;
        $post->status = \App\Enums\PostStatus::Visible->value;
        $post->save();

        return response()->json(['message' => 'Post created successfully', 'data' => $post], 201);
    }

    public function update(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        
        if ($post->user_id !== $request->user()->id && !$request->user()->hasRole('admin|moderator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $post->update($validated);

        return response()->json(['message' => 'Post updated', 'data' => $post]);
    }

    public function destroy(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        if ($post->user_id !== $request->user()->id && !$request->user()->hasRole('admin|moderator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }

    public function acceptAnswer(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        $thread = $post->thread;
        
        if ($thread->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->is_accepted_answer = true;
        $post->save();

        $thread->status = \App\Enums\ThreadStatus::Resolved->value;
        $thread->save();

        return response()->json(['message' => 'Answer accepted', 'data' => $post]);
    }
}
