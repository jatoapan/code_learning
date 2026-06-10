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

    public function show($id) {
        return response()->json(['data' => User::findOrFail($id)]);
    }

    public function deactivate(Request $request, $id) {
        $user = User::findOrFail($id);
        
        abort_unless($request->user()->hasRole('admin'), 403, 'Unauthorized.');
        
        if ($user->hasRole('admin') && $user->id !== $request->user()->id) {
            abort(403, 'No puedes desactivar a otro administrador.');
        }

        $user->status = UserStatus::Deactivated->value;
        $user->save();
        return response()->json(['message' => 'User deactivated']);
    }

    public function updateRole(\App\Http\Requests\User\UpdateRoleRequest $request, $id) {
        $validated = $request->validated();

        $user = User::findOrFail($id);
        
        if ($user->id === $request->user()->id && !in_array('admin', $validated['roles'])) {
            abort(403, 'No puedes remover tu propio rol de administrador.');
        }

        $user->syncRoles($validated['roles']);
        return response()->json(['message' => 'Roles updated', 'data' => $user]);
    }
}
