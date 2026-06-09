<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\CourseUser;
use Illuminate\Http\Request;
use App\Enums\EnrollmentStatus;
use Illuminate\Support\Facades\Gate;

class CourseEnrollmentController extends Controller
{
    public function enroll(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        $pivot = CourseUser::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $request->user()->id],
            ['role' => 'student', 'status' => EnrollmentStatus::Enrolled->value]
        );

        return response()->json(['message' => 'Successfully enrolled', 'data' => $pivot], 201);
    }

    public function drop(Request $request, $id)
    {
        $pivot = CourseUser::where('course_id', $id)
                           ->where('user_id', $request->user()->id)
                           ->firstOrFail();
                           
        $pivot->status = EnrollmentStatus::Dropped->value;
        $pivot->save();

        return response()->json(['message' => 'Course dropped successfully']);
    }

    public function manualEnroll(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'role' => 'required|string|in:student,ta,professor'
        ]);

        $course = Course::findOrFail($id);
        Gate::authorize('update', $course);

        $pivot = CourseUser::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $validated['user_id']],
            ['role' => $validated['role'], 'status' => EnrollmentStatus::Enrolled->value]
        );

        return response()->json(['message' => 'User manually enrolled', 'data' => $pivot], 201);
    }
}
