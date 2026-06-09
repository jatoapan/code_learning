<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use App\Http\Requests\ManualEnrollRequest;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseEnrollmentController extends Controller
{
    public function __construct(private CourseService $courseService) {}

    public function enroll(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        $exists = CourseUser::where('course_id', $course->id)->where('user_id', $request->user()->id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Already enrolled'], 409);
        }

        $pivot = $this->courseService->enrollUser($course, $request->user());

        return response()->json(['message' => 'Successfully enrolled', 'data' => $pivot], 201);
    }

    public function drop(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $this->courseService->dropUser($course, $request->user());

        return response()->json(['message' => 'Course dropped successfully']);
    }

    public function manualEnroll(ManualEnrollRequest $request, $id)
    {
        $course = Course::findOrFail($id);
        Gate::authorize('update', $course);

        $pivot = $this->courseService->manualEnrollUser($course, $request->validated());

        return response()->json(['message' => 'User manually enrolled', 'data' => $pivot], 201);
    }
}
