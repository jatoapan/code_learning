<?php

namespace App\Services;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function updateUser(User $user, array $data, ?UploadedFile $avatarFile = null): User
    {
        if ($avatarFile) {
            // Eliminar avatar anterior si existe
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Guardar nuevo avatar en storage/app/public/avatars/{uuid}.{ext}
            $path = $avatarFile->store('avatars', 'public');
            $data['avatar_path'] = $path;
        }

        // Solo actualizar campos permitidos
        $user->update(array_filter([
            'name'        => $data['name'] ?? null,
            'avatar_path' => $data['avatar_path'] ?? null,
        ], fn($v) => !is_null($v)));

        return $user->fresh();
    }

    public function deactivateUser(User $user): void
    {
        $user->update(['status' => UserStatus::Deactivated->value]);
    }
}
