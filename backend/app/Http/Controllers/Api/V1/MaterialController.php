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

class MaterialController extends Controller
{
    public function __construct(private SyllabusService $syllabusService) {}

    public function show($id)
    {
        $material = Material::findOrFail($id);
        return response()->json(['data' => $material]);
    }

    public function store(StoreMaterialRequest $request, $id)
    {
        $module = Module::findOrFail($id);
        Gate::authorize('update', $module->course);

        $file = $request->file('file');
        
        $material = $this->syllabusService->createMaterial(
            $module, 
            $request->validated(), 
            $file, 
            $request->user()
        );

        return response()->json(['message' => 'Material created successfully', 'data' => $material], 201);
    }

    public function update(UpdateMaterialRequest $request, $id)
    {
        $material = Material::findOrFail($id);
        
        $moduleItem = $material->moduleItems()->with('module.course')->first();
        if ($moduleItem) {
            Gate::authorize('update', $moduleItem->module->course);
        }

        $material = $this->syllabusService->updateMaterial($material, $request->validated());

        return response()->json(['message' => 'Material updated', 'data' => $material]);
    }

    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        
        $moduleItem = $material->moduleItems()->with('module.course')->first();
        if ($moduleItem) {
            Gate::authorize('update', $moduleItem->module->course);
        }

        $this->syllabusService->deleteMaterial($material);
        
        return response()->json(['message' => 'Material deleted']);
    }

    public function recordView(Request $request, $id)
    {
        return response()->json(['message' => 'Material view recorded']);
    }

    /**
     * Secure Document Viewer Endpoint
     */
    public function download($id)
    {
        $material = Material::findOrFail($id);
        
        $user = auth()->user();
        $moduleItem = $material->moduleItems()->with('module')->first();
        if ($moduleItem) {
            $courseId = $moduleItem->module->course_id;
            $isEnrolled = \App\Models\CourseUser::where('course_id', $courseId)->where('user_id', $user->id)->exists();
            $isOwner = \App\Models\Course::where('id', $courseId)->where('owner_id', $user->id)->exists();
            $isAdmin = $user->hasRole('admin') || $user->hasRole('moderator');
            
            if (!$isEnrolled && !$isOwner && !$isAdmin) {
                return response()->json(['message' => 'Acceso denegado: No estás matriculado en este curso.'], 403);
            }
        }
        
        // Si es un link externo (ej. Youtube), redirigimos de forma transparente
        if ($material->type === 'video_link' || str_starts_with($material->file_path, 'http')) {
            return redirect($material->file_path);
        }

        // Si es un PDF o Video pesado de nuestra bóveda, comprobamos su existencia
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($material->file_path)) {
            return response()->json(['message' => 'Archivo protegido no encontrado'], 404);
        }

        // Transmisión Segura (Streaming): Solo el usuario con JWT llega hasta aquí
        return \Illuminate\Support\Facades\Storage::disk('local')->download($material->file_path, $material->title);
    }
}
