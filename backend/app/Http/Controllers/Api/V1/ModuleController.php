<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(Request $request, $courseId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'prerequisite_module_id' => 'nullable|integer',
        ]);

        return response()->json(['message' => 'Module created', 'data' => $validated], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'prerequisite_module_id' => 'nullable|integer',
        ]);

        return response()->json(['message' => 'Module updated', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Module deleted']);
    }

    public function reorderItems(Request $request, $id)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.itemable_type' => 'required|string',
            'items.*.itemable_id' => 'required|string',
            'items.*.order' => 'required|integer',
        ]);

        return response()->json(['message' => 'Module items reordered', 'data' => $validated]);
    }
}
