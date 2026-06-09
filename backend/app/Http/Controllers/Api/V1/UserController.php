<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255'
        ]);
        
        $request->user()->update($validated);
        return response()->json($request->user());
    }

    public function deactivate(Request $request)
    {
        $request->user()->update(['status' => \App\Enums\UserStatus::Deactivated]);
        auth('api')->logout(); // Invalida el token JWT en lugar de Sanctum
        
        return response()->json(['message' => 'Cuenta desactivada permanentemente']);
    }
}
