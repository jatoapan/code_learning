<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(private UserService $userService, private AuthService $authService)
    {
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $data = $user->toArray();
        $data['avatar_url'] = $user->avatar_path
            ? Storage::disk('public')->url($user->avatar_path)
            : null;
        return response()->json($data);
    }

    public function update(UpdateUserRequest $request)
    {
        $user = $this->userService->updateUser(
            $request->user(),
            $request->validated(),
            $request->file('avatar')
        );

        $data = $user->toArray();
        $data['avatar_url'] = $user->avatar_path
            ? Storage::disk('public')->url($user->avatar_path)
            : null;

        return response()->json($data);
    }

    public function deactivate(Request $request)
    {
        $this->userService->deactivateUser($request->user());
        $this->authService->logoutUser();

        return response()->json(['message' => 'Cuenta desactivada permanentemente']);
    }
}
