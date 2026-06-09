<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use App\Services\CourseService;
use App\Http\Requests\StoreCourseRequest;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService) {}

    public function index(Request $request)
    {
        $courses = Course::with('owner:id,name,avatar_path')->paginate(15);
        return response()->json(['data' => $courses]);
    }

    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->createCourse($request->validated(), $request->user());
        return response()->json(['message' => 'Course created successfully', 'data' => $course], 201);
    }

    public function show($id)
    {
        $course = Course::with(['modules.items.itemable', 'owner:id,name'])->findOrFail($id);
        
        Gate::authorize('view', $course);

        return response()->json(['data' => $course]);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        Gate::authorize('update', $course);
        
        $validated = $request->validate([
            'category' => 'sometimes|required|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image_path' => 'nullable|string|max:255',
            'status' => 'sometimes|required|string|in:draft,public,unlisted',
            'has_leaderboard' => 'boolean',
        ]);

        if (isset($validated['title'])) {
            $course->slug = Str::slug($validated['title']) . '-' . uniqid();
        }
        $course->update($validated);

        return response()->json(['message' => 'Course updated', 'data' => $course]);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        Gate::authorize('update', $course);
        
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }

    public function progress($id)
    {
        $pivot = CourseUser::where('course_id', $id)->where('user_id', request()->user()->id)->first();
        return response()->json(['data' => ['progress_percent' => $pivot ? $pivot->progress_percent : 0]]);
    }

    public function leaderboard($id)
    {
        $leaderboard = CourseUser::with('user:id,name,avatar_path')
            ->where('course_id', $id)
            ->where('role', 'student')
            ->orderBy('xp', 'desc')
            ->limit(50)
            ->get();
            
        return response()->json(['data' => $leaderboard]);
    }

    public function stats($id)
    {
        $totalStudents = CourseUser::where('course_id', $id)->where('role', 'student')->count();
        return response()->json(['data' => ['total_students' => $totalStudents]]);
    }

    public function addStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'role' => 'required|string|in:professor,ta',
        ]);

        $pivot = CourseUser::firstOrCreate([
            'course_id' => $id,
            'user_id' => $validated['user_id'],
        ], [
            'role' => $validated['role'],
            'status' => 'enrolled'
        ]);

        return response()->json(['message' => 'Staff added', 'data' => $pivot]);
    }

    public function removeStaff($id, $userId)
    {
        CourseUser::where('course_id', $id)->where('user_id', $userId)->delete();
        return response()->json(['message' => 'Staff removed']);
    }

    public function analytics($id)
    {
        return response()->json(['message' => 'Course analytics']);
    }
}
