<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserStatus;

class SupportUserController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);
        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        return response()->json(['message' => 'Support ticket created successfully', 'data' => $validated], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['data' => $user]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string',
        ]);

        return response()->json(['message' => 'Support ticket updated successfully', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Support ticket deleted successfully']);
    }

    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->status = UserStatus::Deactivated->value;
        $user->save();

        return response()->json(['message' => 'User deactivated successfully', 'data' => $user]);
    }

    public function updateRole(Request $request, $id)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles($validated['roles']);

        return response()->json(['message' => 'Roles updated successfully', 'data' => $user]);
    }
}
