<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    public function store(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
        ]);

        $module = new Module($validated);
        $module->course_id = $course->id;
        $module->save();

        return response()->json(['message' => 'Module created successfully', 'data' => $module], 201);
    }

    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        Gate::authorize('update', $module->course);

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
        Gate::authorize('update', $module->course);
        
        $module->delete();
        
        return response()->json(['message' => 'Module deleted']);
    }

    public function reorderItems(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        Gate::authorize('update', $module->course);
        
        return response()->json(['message' => 'Items reordered']);
    }
}
