<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function __construct(private UserService $userService, private AuthService $authService)
    {
    }

    public function me(Request $request)
    {
        return response()->json(new UserResource($request->user()));
    }

    public function update(UpdateUserRequest $request)
    {
        $user = $this->userService->updateUser(
            $request->user(),
            $request->validated(),
            $request->file('avatar')
        );

        return response()->json(new UserResource($user));
    }

    public function deactivate(Request $request)
    {
        $this->userService->deactivateUser($request->user());
        $this->authService->logoutUser();

        return response()->json(['message' => 'Cuenta desactivada permanentemente']);
    }
}
