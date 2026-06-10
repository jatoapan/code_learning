<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserStatus;

class SupportUserController extends Controller
{
    public function index() {
        return response()->json(['data' => User::paginate(20)]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        return response()->json(['message' => 'Support ticket created', 'data' => $validated], 201);
    }

    public function show($id) {
        return response()->json(['data' => User::findOrFail($id)]);
    }

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string',
        ]);
        return response()->json(['message' => 'Support ticket updated', 'data' => $validated]);
    }

    public function destroy($id) {
        return response()->json(['message' => 'Support ticket deleted']);
    }

    public function deactivate(Request $request, $id) {
        $user = User::findOrFail($id);
        
        if ($user->hasRole('admin') && !$request->user()->hasRole('admin')) {
            abort(403, 'No puedes desactivar a un administrador.');
        }

        $user->status = UserStatus::Deactivated->value;
        $user->save();
        return response()->json(['message' => 'User deactivated']);
    }

    public function updateRole(Request $request, $id) {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        
        if ($user->id === $request->user()->id && $request->user()->hasRole('admin') && !in_array('admin', $validated['roles'])) {
            abort(403, 'No puedes remover tu propio rol de administrador.');
        }

        if (!$request->user()->hasRole('admin') && in_array('admin', $validated['roles'])) {
            abort(403, 'Solo un administrador puede asignar el rol de admin.');
        }

        $user->syncRoles($validated['roles']);
        return response()->json(['message' => 'Roles updated', 'data' => $user]);
    }
}
