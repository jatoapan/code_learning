<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Course;
use App\Http\Requests\StoreModuleRequest;
use App\Http\Requests\UpdateModuleRequest;
use App\Services\SyllabusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    public function __construct(private SyllabusService $syllabusService) {}

    public function store(StoreModuleRequest $request, $id)
    {
        $course = Course::findOrFail($id);
        Gate::authorize('update', $course);

        $module = $this->syllabusService->createModule($course, $request->validated());

        return response()->json(['message' => 'Module created successfully', 'data' => $module], 201);
    }

    public function update(UpdateModuleRequest $request, $id)
    {
        $module = Module::findOrFail($id);
        Gate::authorize('update', $module->course);

        $module = $this->syllabusService->updateModule($module, $request->validated());

        return response()->json(['message' => 'Module updated', 'data' => $module]);
    }

    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        Gate::authorize('update', $module->course);
        
        $this->syllabusService->deleteModule($module);
        
        return response()->json(['message' => 'Module deleted']);
    }

    public function reorderItems(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        Gate::authorize('update', $module->course);
        
        return response()->json(['message' => 'Items reordered']);
    }
}
