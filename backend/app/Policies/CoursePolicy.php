<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update/manage the course.
     */
    public function update(User $user, Course $course)
    {
        // 1. Is the absolute owner
        if ($course->owner_id === $user->id) {
            return true;
        }

        // 2. Is Global Admin or Moderator
        if ($user->hasRole('admin') || $user->hasRole('moderator')) {
            return true;
        }

        // 3. Is explicitly enrolled as a TA or Professor in THIS specific course
        return \App\Models\CourseUser::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['ta', 'professor'])
            ->exists();
    }
}
