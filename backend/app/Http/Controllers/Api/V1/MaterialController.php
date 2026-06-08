<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Module;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function show($id)
    {
        $material = Material::findOrFail($id);
        return response()->json(['data' => $material]);
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:video,reading,link',
            'content' => 'required|string',
            'estimated_minutes' => 'required|integer|min:1',
            'is_required' => 'boolean',
        ]);

        $module = Module::findOrFail($id);

        $material = new Material($validated);
        $material->module_id = $module->id;
        $material->save();

        return response()->json(['message' => 'Material created successfully', 'data' => $material], 201);
    }

    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:video,reading,link',
            'content' => 'sometimes|required|string',
            'estimated_minutes' => 'sometimes|required|integer|min:1',
            'is_required' => 'boolean',
        ]);

        $material->update($validated);

        return response()->json(['message' => 'Material updated', 'data' => $material]);
    }

    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();
        return response()->json(['message' => 'Material deleted']);
    }

    public function recordView(Request $request, $id)
    {
        return response()->json(['message' => 'Material view recorded']);
    }
}
