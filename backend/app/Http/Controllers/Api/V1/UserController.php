<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\AnonymizeUserAction;

class UserController extends Controller
{
    /**
     * Obtiene el perfil del usuario autenticado.
     */
    public function me(Request $request)
    {
        // Se asume que la relación institution existe en el modelo User
        return response()->json($request->user()->load('institution'));
    }

    /**
     * Actualiza metadatos del perfil.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar_path' => 'sometimes|string|max:255',
            'institution_id' => 'sometimes|nullable|exists:institutions,id'
        ]);

        $request->user()->update($validated);

        return response()->json($request->user());
    }

    /**
     * Desactiva y anonimiza la cuenta del usuario inmediatamente.
     */
    public function deactivate(Request $request, AnonymizeUserAction $anonymizeUserAction)
    {
        $anonymizeUserAction->execute($request->user());
        
        return response()->json([
            'message' => 'Cuenta desactivada y datos personales anonimizados de manera irreversible.'
        ], 200);
    }
}
