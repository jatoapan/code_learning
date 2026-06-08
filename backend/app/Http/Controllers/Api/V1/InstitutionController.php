<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        $institutions = Institution::paginate(20);
        return response()->json(['data' => $institutions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:institutions',
            'domain' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
        ]);

        $institution = Institution::create($validated);

        return response()->json(['message' => 'Institution created', 'data' => $institution], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:institutions,slug,'.$id,
            'domain' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'type' => 'sometimes|required|string|max:50',
        ]);

        $institution = Institution::findOrFail($id);
        $institution->update($validated);

        return response()->json(['message' => 'Institution updated', 'data' => $institution]);
    }

    public function destroy($id)
    {
        $institution = Institution::findOrFail($id);
        $institution->delete();

        return response()->json(['message' => 'Institution deleted']);
    }

    public function analytics($id)
    {
        $institution = Institution::withCount(['users'])->findOrFail($id);
        
        $stats = [
            'users_count' => $institution->users_count,
        ];

        return response()->json(['data' => $stats]);
    }
}
