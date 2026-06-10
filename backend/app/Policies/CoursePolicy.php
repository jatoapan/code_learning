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

    public function view(?User $user, Course $course): bool
    {
        if ($course->status->value === 'public') return true;
        if (!$user) return false;
        
        if ($user->hasRole('admin') || $user->hasRole('moderator')) return true;
        if ($course->owner_id === $user->id) return true;

        return \App\Models\CourseUser::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function manageCourse(User $user, Course $course)
    {
        return $course->owner_id === $user->id || $user->hasRole('admin');
    }

    public function manageStaff(User $user, Course $course)
    {
        return $course->owner_id === $user->id || $user->hasRole('admin');
    }

    public function manageEnrollments(User $user, Course $course)
    {
        return $course->owner_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the course.
     * Solo el dueño absoluto o un admin global puede borrar el curso. Los TAs no.
     */
    public function delete(User $user, Course $course)
    {
        return $course->owner_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can remove a staff member.
     */
    public function removeStaff(User $user, Course $course, string $targetUserId)
    {
        if (!$this->manageStaff($user, $course)) {
            return false;
        }

        if ($course->owner_id === $targetUserId) {
            return false;
        }

        return true;
    }
}
