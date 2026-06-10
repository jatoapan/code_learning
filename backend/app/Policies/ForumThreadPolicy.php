<?php

namespace App\Policies;

use App\Models\ForumThread;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumThreadPolicy
{
    use HandlesAuthorization;

    private function getCourse(ForumThread $thread)
    {
        if ($thread->forumable_type === Course::class) {
            return $thread->forumable;
        }
        if ($thread->forumable_type === Module::class) {
            return $thread->forumable->course;
        }
        if ($thread->forumable_type === Challenge::class) {
            return $thread->forumable->module->course;
        }
        return null;
    }

    public function view(User $user, ForumThread $thread)
    {
        $course = $this->getCourse($thread);
        return $course ? \Illuminate\Support\Facades\Gate::forUser($user)->check('view', $course) : false;
    }

    public function update(User $user, ForumThread $thread)
    {
        $course = $this->getCourse($thread);
        return $course ? \Illuminate\Support\Facades\Gate::forUser($user)->check('update', $course) : false;
    }
}
