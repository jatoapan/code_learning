<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function store(Request $request, $moduleId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:50',
            'file_path' => 'required|string|max:255',
            'file_size' => 'nullable|integer',
        ]);

        return response()->json(['message' => 'Material created', 'data' => $validated], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Material details']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|string|max:50',
            'file_path' => 'sometimes|required|string|max:255',
            'file_size' => 'nullable|integer',
        ]);

        return response()->json(['message' => 'Material updated', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Material deleted']);
    }

    public function registerView($id)
    {
        return response()->json(['message' => 'Material view registered']);
    }

    public function endorse($id)
    {
        return response()->json(['message' => 'Material endorsed']);
    }

    public function removeEndorsement($id)
    {
        return response()->json(['message' => 'Material endorsement removed']);
    }
}
