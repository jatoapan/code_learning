<?php

namespace App\Policies;

use App\Models\ProfessorApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfessorApplicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, ProfessorApplication $professorApplication)
    {
        return $user->hasRole('admin') || $user->id === $professorApplication->applicant_id;
    }

    public function manage(User $user)
    {
        return $user->hasRole('admin');
    }

    public function review(User $user, ProfessorApplication $professorApplication)
    {
        return $user->hasRole('admin') || $user->id === $professorApplication->reviewer_id;
    }
}
