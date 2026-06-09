<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ForumService;
use Illuminate\Http\Request;

class ForumThreadController extends Controller
{
    protected ForumService $forumService;

    public function __construct(ForumService $forumService)
    {
        $this->forumService = $forumService;
    }

    public function indexByCourse($id)
    {
        $threads = $this->forumService->getThreadsByCourse($id);
        return response()->json(['data' => $threads]);
    }

    public function indexByModule($id)
    {
        $threads = $this->forumService->getThreadsByModule($id);
        return response()->json(['data' => $threads]);
    }

    public function indexByChallenge($id)
    {
        $threads = $this->forumService->getThreadsByChallenge($id);
        return response()->json(['data' => $threads]);
    }

    public function storeCourseThread(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $thread = $this->forumService->createCourseThread($validated, $id, $request->user()->id);

        return response()->json(['message' => 'Thread created successfully', 'data' => $thread], 201);
    }

    public function storeModuleThread(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $thread = $this->forumService->createModuleThread($validated, $id, $request->user()->id);

        return response()->json(['message' => 'Thread created successfully', 'data' => $thread], 201);
    }

    public function storeChallengeThread(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $thread = $this->forumService->createChallengeThread($validated, $id, $request->user()->id);

        return response()->json(['message' => 'Thread created successfully', 'data' => $thread], 201);
    }

    public function show($id)
    {
        $thread = $this->forumService->getThread($id);
        return response()->json(['data' => $thread]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        $thread = $this->forumService->updateThread($id, $validated, $request->user());

        return response()->json(['message' => 'Thread updated', 'data' => $thread]);
    }

    public function destroy(Request $request, $id)
    {
        $this->forumService->deleteThread($id, $request->user());
        return response()->json(['message' => 'Thread deleted']);
    }

    public function togglePin(Request $request, $id)
    {
        $thread = $this->forumService->togglePinThread($id, $request->user());
        return response()->json(['message' => 'Thread pin toggled', 'data' => $thread]);
    }

    public function lock(Request $request, $id)
    {
        $thread = $this->forumService->lockThread($id, $request->user());
        return response()->json(['message' => 'Thread locked', 'data' => $thread]);
    }
}
