<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->registerUser($request->validated());
        return response()->json($result, 201);
    }

    public function login(LoginRequest $request)
    {
        if (! $token = $this->authService->loginUser($request->validated())) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => auth('api')->user()
        ], 200);
    }

    public function logout(Request $request)
    {
        $this->authService->logoutUser();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    public function sendResetLink(\App\Http\Requests\Auth\SendResetLinkRequest $request)
    {
        $status = $this->authService->sendResetLink($request->validated());

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Enlace de recuperación enviado'])
            : response()->json(['message' => 'No se pudo enviar el enlace'], 422);
    }

    public function resetPassword(\App\Http\Requests\Auth\ResetPasswordRequest $request)
    {
        $status = $this->authService->resetPassword($request->validated());

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? response()->json(['message' => 'Contraseña actualizada'])
            : response()->json(['message' => 'Token inválido o expirado'], 422);
    }
}
