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

    public function enroll(Request $request, $id) {
        $course = Course::findOrFail($id);
        
        if ($course->status !== 'public' && !Gate::allows('update', $course)) {
            abort(403, 'No puedes matricularte en un curso privado o en borrador.');
        }
        
        if (CourseUser::where('course_id', $course->id)->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Already enrolled'], 409);
        }

        return response()->json(['message' => 'Successfully enrolled', 'data' => $this->courseService->enrollUser($course, $request->user())], 201);
    }

    public function drop(Request $request, $id) {
        $course = Course::findOrFail($id);
        $this->courseService->dropUser($course, $request->user());
        return response()->json(['message' => 'Course dropped successfully']);
    }

    public function manualEnroll(ManualEnrollRequest $request, $id) {
        $course = Course::findOrFail($id);
        Gate::authorize('update', $course);
        return response()->json(['message' => 'User manually enrolled', 'data' => $this->courseService->manualEnrollUser($course, $request->validated())], 201);
    }
}
