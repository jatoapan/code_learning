<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ForumService;
use Illuminate\Http\Request;

class ForumPostController extends Controller
{
    protected ForumService $forumService;

    public function __construct(ForumService $forumService)
    {
        $this->forumService = $forumService;
    }

    public function store(Request $request, $threadId)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $post = $this->forumService->createPost($validated, $threadId, $request->user()->id);

        return response()->json(['message' => 'Post created successfully', 'data' => $post], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $post = $this->forumService->updatePost($id, $validated, $request->user());

        return response()->json(['message' => 'Post updated', 'data' => $post]);
    }

    public function destroy(Request $request, $id)
    {
        $this->forumService->deletePost($id, $request->user());
        return response()->json(['message' => 'Post deleted']);
    }

    public function acceptAnswer(Request $request, $id)
    {
        $post = $this->forumService->acceptAnswer($id, $request->user());
        return response()->json(['message' => 'Answer accepted', 'data' => $post]);
    }
}
