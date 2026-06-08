<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['message' => 'List of institutions']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
        ]);

        return response()->json(['message' => 'Institution created', 'data' => $validated], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'domain' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'type' => 'sometimes|required|string|max:50',
        ]);

        return response()->json(['message' => 'Institution updated', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Institution deleted']);
    }

    public function analytics($id)
    {
        return response()->json(['message' => 'Institution analytics']);
    }
}
