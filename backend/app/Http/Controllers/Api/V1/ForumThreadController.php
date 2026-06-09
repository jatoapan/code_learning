<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use App\Models\ForumThread;
use App\Services\ForumService;
use App\Http\Requests\Forum\StoreThreadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ForumThreadController extends Controller
{
    public function __construct(private ForumService $forumService) {}

    private function authorizeThreadAccess(ForumThread $thread) {
        $course = null;
        if ($thread->forumable_type === Course::class) $course = $thread->forumable;
        if ($thread->forumable_type === Module::class) $course = $thread->forumable->course;
        if ($thread->forumable_type === Challenge::class) $course = $thread->forumable->module->course;
        
        if ($course) Gate::authorize('view', $course);
    }

    public function indexByCourse($id) {
        $course = Course::findOrFail($id);
        Gate::authorize('view', $course);
        return response()->json(['data' => $this->forumService->getThreadsByCourse($id)]);
    }

    public function indexByModule($id) {
        $module = Module::with('course')->findOrFail($id);
        Gate::authorize('view', $module->course);
        return response()->json(['data' => $this->forumService->getThreadsByModule($id)]);
    }

    public function indexByChallenge($id) {
        $challenge = Challenge::with('module.course')->findOrFail($id);
        Gate::authorize('view', $challenge->module->course);
        return response()->json(['data' => $this->forumService->getThreadsByChallenge($id)]);
    }

    public function storeCourseThread(StoreThreadRequest $request, $id) {
        $course = Course::findOrFail($id);
        Gate::authorize('view', $course); // Solo matriculados escriben
        return response()->json(['message' => 'Thread created', 'data' => $this->forumService->createCourseThread($request->validated(), $id, $request->user()->id)], 201);
    }

    public function storeModuleThread(StoreThreadRequest $request, $id) {
        $module = Module::with('course')->findOrFail($id);
        Gate::authorize('view', $module->course);
        return response()->json(['message' => 'Thread created', 'data' => $this->forumService->createModuleThread($request->validated(), $id, $request->user()->id)], 201);
    }

    public function storeChallengeThread(StoreThreadRequest $request, $id) {
        $challenge = Challenge::with('module.course')->findOrFail($id);
        Gate::authorize('view', $challenge->module->course);
        return response()->json(['message' => 'Thread created', 'data' => $this->forumService->createChallengeThread($request->validated(), $id, $request->user()->id)], 201);
    }

    public function show($id) {
        $thread = ForumThread::with('forumable')->findOrFail($id);
        $this->authorizeThreadAccess($thread);
        
        return response()->json(['data' => $this->forumService->getThread($id)]);
    }

    public function update(StoreThreadRequest $request, $id) {
        return response()->json(['message' => 'Thread updated', 'data' => $this->forumService->updateThread($id, $request->validated(), $request->user())]);
    }

    public function destroy(Request $request, $id) {
        $this->forumService->deleteThread($id, $request->user());
        return response()->json(['message' => 'Thread deleted']);
    }

    public function togglePin(Request $request, $id) {
        return response()->json(['message' => 'Thread pin toggled', 'data' => $this->forumService->togglePinThread($id, $request->user())]);
    }

    public function lock(Request $request, $id) {
        return response()->json(['message' => 'Thread locked', 'data' => $this->forumService->lockThread($id, $request->user())]);
    }
}
