<?php

namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuizAttemptPolicy
{
    use HandlesAuthorization;

    public function view(User $user, QuizAttempt $attempt)
    {
        return $attempt->user_id === $user->id || $user->hasRole('admin|professor|ta');
    }
}
