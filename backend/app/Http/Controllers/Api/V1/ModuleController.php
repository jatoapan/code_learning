<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Course;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ]);

        $course = Course::findOrFail($id);

        $module = new Module($validated);
        $module->course_id = $course->id;
        $module->save();

        return response()->json(['message' => 'Module created successfully', 'data' => $module], 201);
    }

    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|required|integer',
        ]);

        $module->update($validated);

        return response()->json(['message' => 'Module updated', 'data' => $module]);
    }

    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();
        return response()->json(['message' => 'Module deleted']);
    }

    public function reorderItems(Request $request, $id)
    {
        return response()->json(['message' => 'Items reordered']);
    }
}
