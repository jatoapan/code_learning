<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use App\Services\ForumService;
use App\Http\Requests\Forum\StorePostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ForumPostController extends Controller
{
    public function __construct(private ForumService $forumService) {}

    private function authorizeThreadAccess(ForumThread $thread) {
        $course = null;
        if ($thread->forumable_type === Course::class) $course = $thread->forumable;
        if ($thread->forumable_type === Module::class) $course = $thread->forumable->course;
        if ($thread->forumable_type === Challenge::class) $course = $thread->forumable->module->course;
        
        if ($course) Gate::authorize('view', $course);
    }

    public function store(StorePostRequest $request, $threadId) {
        $thread = ForumThread::with('forumable')->findOrFail($threadId);
        $this->authorizeThreadAccess($thread); // Prevenir inyección en cursos ajenos
        
        return response()->json(['message' => 'Post created', 'data' => $this->forumService->createPost($request->validated(), $threadId, $request->user()->id)], 201);
    }

    public function update(StorePostRequest $request, $id) {
        return response()->json(['message' => 'Post updated', 'data' => $this->forumService->updatePost($id, $request->validated(), $request->user())]);
    }

    public function destroy(Request $request, $id) {
        $this->forumService->deletePost($id, $request->user());
        return response()->json(['message' => 'Post deleted']);
    }

    public function acceptAnswer(Request $request, $id) {
        return response()->json(['message' => 'Answer accepted', 'data' => $this->forumService->acceptAnswer($id, $request->user())]);
    }
}
