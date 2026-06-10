<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\CourseService;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Requests\AddStaffRequest;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService) {}

    public function index(Request $request) {
        return response()->json(['data' => $this->courseService->getPaginatedCourses()]);
    }

    public function store(StoreCourseRequest $request) {
        $course = $this->courseService->createCourse($request->validated(), $request->user());
        return response()->json(['message' => 'Course created successfully', 'data' => $course], 201);
    }

    public function show($id) {
        $course = $this->courseService->getCourseWithDetails($id);
        Gate::authorize('view', $course);
        return response()->json(['data' => $course]);
    }

    public function update(UpdateCourseRequest $request, $id) {
        $course = Course::findOrFail($id);
        Gate::authorize('manageCourse', $course);
        return response()->json([
            'message' => 'Course updated',
            'data' => $this->courseService->updateCourse($course, $request->validated())
        ]);
    }

    public function destroy($id) {
        $course = Course::findOrFail($id);
        Gate::authorize('delete', $course); // Corregido: solo el owner puede borrar
        $this->courseService->deleteCourse($course);
        return response()->json(['message' => 'Course deleted']);
    }

    public function progress($id, Request $request) {
        $course = Course::findOrFail($id);
        Gate::authorize('view', $course);
        return response()->json(['data' => ['progress_percent' => $this->courseService->getProgress($course, $request->user()->id)]]);
    }

    public function leaderboard($id) {
        $course = Course::findOrFail($id);
        Gate::authorize('view', $course);
        return response()->json(['data' => $this->courseService->getLeaderboard($course)]);
    }

    public function stats($id) {
        $course = Course::findOrFail($id);
        Gate::authorize('view', $course);
        return response()->json(['data' => $this->courseService->getStats($course)]);
    }

    public function addStaff(AddStaffRequest $request, $id) {
        $course = Course::findOrFail($id);
        Gate::authorize('manageStaff', $course); // Mitiga IDOR
        $pivot = $this->courseService->addStaff($course, $request->validated());
        return response()->json(['message' => 'Staff added', 'data' => $pivot]);
    }

    public function removeStaff($id, $userId) {
        $course = Course::findOrFail($id);
        Gate::authorize('removeStaff', [$course, $userId]);

        $this->courseService->removeStaff($course, $userId);
        return response()->json(['message' => 'Staff removed']);
    }

}
