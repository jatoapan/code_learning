<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserStatus;

class UserService
{
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function deactivateUser(User $user): void
    {
        $user->update(['status' => UserStatus::Deactivated->value]);
    }
}
