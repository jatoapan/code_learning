<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function registerUser(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'xp' => 0
        ]);

        $token = auth('api')->login($user);

        return [
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]
        ];
    }

    public function loginUser(array $credentials): ?string
    {
        return auth('api')->attempt($credentials) ?: null;
    }

    public function logoutUser(): void
    {
        auth('api')->logout();
    }

    public function sendResetLink(array $data): string
    {
        return \Illuminate\Support\Facades\Password::sendResetLink($data);
    }

    public function resetPassword(array $data): string
    {
        return \Illuminate\Support\Facades\Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill(['password' => \Illuminate\Support\Facades\Hash::make($password)])->save();
            }
        );
    }
}
