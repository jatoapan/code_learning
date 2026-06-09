<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Module;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Services\SyllabusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function __construct(private SyllabusService $syllabusService) {}

    public function show($id) {
        $material = Material::findOrFail($id);
        $moduleItem = $material->moduleItems()->first();
        if ($moduleItem) Gate::authorize('view', $moduleItem->module->course);
        
        return response()->json(['data' => $material]);
    }

    public function store(StoreMaterialRequest $request, $id) {
        $module = Module::findOrFail($id);
        Gate::authorize('update', $module->course);
        $material = $this->syllabusService->createMaterial($module, $request->validated(), $request->file('file'), $request->user());
        return response()->json(['message' => 'Material created', 'data' => $material], 201);
    }

    public function update(UpdateMaterialRequest $request, $id) {
        $material = Material::findOrFail($id);
        $moduleItem = $material->moduleItems()->first();
        if ($moduleItem) Gate::authorize('update', $moduleItem->module->course);

        return response()->json(['message' => 'Material updated', 'data' => $this->syllabusService->updateMaterial($material, $request->validated())]);
    }

    public function destroy($id) {
        $material = Material::findOrFail($id);
        $moduleItem = $material->moduleItems()->first();
        if ($moduleItem) Gate::authorize('update', $moduleItem->module->course);

        $this->syllabusService->deleteMaterial($material);
        return response()->json(['message' => 'Material deleted']);
    }

    public function recordView(Request $request, $id) {
        $material = Material::findOrFail($id);
        $moduleItem = $material->moduleItems()->first();
        if ($moduleItem) Gate::authorize('view', $moduleItem->module->course);
        
        $this->syllabusService->recordMaterialView($material, $request->user());
        return response()->json(['message' => 'Material view recorded']);
    }

    public function download($id) {
        $material = Material::findOrFail($id);
        
        $moduleItem = $material->moduleItems()->first();
        if ($moduleItem) {
            Gate::authorize('view', $moduleItem->module->course);
        }

        if ($material->type === 'video_link' || str_starts_with($material->file_path, 'http')) {
            return redirect($material->file_path);
        }

        if (!Storage::disk('local')->exists($material->file_path)) {
            return response()->json(['message' => 'Archivo protegido no encontrado'], 404);
        }

        return Storage::disk('local')->download($material->file_path, $material->title);
    }
}
