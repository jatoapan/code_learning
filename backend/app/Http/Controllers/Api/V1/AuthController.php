<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'xp' => 0
        ]);

        return response()->json([
            'token' => $user->createToken($request->device_name ?? 'web')->plainTextToken,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        return response()->json([
            'token' => $user->createToken($request->device_name ?? 'web')->plainTextToken,
            'user' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        // El usuario revoca el token actual con el que hizo la petición
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    public function sendResetLink(Request $request) { 
        return response()->json(['message' => 'Enlace de recuperación enviado']); 
    }
    
    public function resetPassword(Request $request) { 
        return response()->json(['message' => 'Contraseña actualizada']); 
    }
}
