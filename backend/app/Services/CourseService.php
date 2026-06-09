<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CourseService
{
    public function createCourse(array $data, $user): Course
    {
        return DB::transaction(function () use ($data, $user) {
            $course = new Course($data);
            $course->owner_id = $user->id;
            $course->slug = Str::slug($data['title']) . '-' . uniqid();
            $course->save();

            CourseUser::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'role' => 'professor',
                'status' => 'enrolled'
            ]);

            return $course;
        });
    }

    public function enrollUser(Course $course, $user): CourseUser
    {
        return DB::transaction(function () use ($course, $user) {
            return CourseUser::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'role' => 'student',
                'status' => \App\Enums\EnrollmentStatus::Enrolled->value
            ]);
        });
    }

    public function dropUser(Course $course, $user): void
    {
        DB::transaction(function () use ($course, $user) {
            $pivot = CourseUser::where('course_id', $course->id)
                               ->where('user_id', $user->id)
                               ->firstOrFail();
                               
            $pivot->status = \App\Enums\EnrollmentStatus::Dropped->value;
            $pivot->save();
        });
    }

    public function manualEnrollUser(Course $course, array $data): CourseUser
    {
        return DB::transaction(function () use ($course, $data) {
            return CourseUser::updateOrCreate(
                ['course_id' => $course->id, 'user_id' => $data['user_id']],
                ['role' => $data['role'], 'status' => \App\Enums\EnrollmentStatus::Enrolled->value]
            );
        });
    }
    public function updateCourse(Course $course, array $data): Course {
        if (isset($data['title'])) {
            $course->slug = Str::slug($data['title']) . '-' . uniqid();
        }
        $course->update($data);
        return $course;
    }

    public function deleteCourse(Course $course): void {
        $course->delete();
    }

    public function getProgress(Course $course, string $userId): int {
        $pivot = CourseUser::where('course_id', $course->id)->where('user_id', $userId)->first();
        return $pivot ? $pivot->progress_percent : 0;
    }

    public function getLeaderboard(Course $course) {
        return CourseUser::with('user:id,name,avatar_path')
            ->where('course_id', $course->id)
            ->where('role', 'student')
            ->orderBy('xp', 'desc')
            ->limit(50)
            ->get();
    }

    public function getStats(Course $course): array {
        $totalStudents = CourseUser::where('course_id', $course->id)->where('role', 'student')->count();
        return ['total_students' => $totalStudents];
    }

    public function addStaff(Course $course, array $data): CourseUser {
        return CourseUser::firstOrCreate(
            ['course_id' => $course->id, 'user_id' => $data['user_id']],
            ['role' => $data['role'], 'status' => 'enrolled']
        );
    }

    public function removeStaff(Course $course, string $userId): void {
        CourseUser::where('course_id', $course->id)->where('user_id', $userId)->delete();
    }
}
