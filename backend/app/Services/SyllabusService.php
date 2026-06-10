<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Material;
use App\Models\ModuleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class SyllabusService
{
    public function createModule(Course $course, array $data): Module
    {
        return DB::transaction(function () use ($course, $data) {
            $module = new Module($data);
            $module->course_id = $course->id;
            $module->save();

            return $module;
        });
    }

    public function updateModule(Module $module, array $data): Module
    {
        return DB::transaction(function () use ($module, $data) {
            $module->update($data);
            return $module;
        });
    }

    public function deleteModule(Module $module): void
    {
        DB::transaction(function () use ($module) {
            $module->delete();
        });
    }

    public function createMaterial(Module $module, array $data, ?UploadedFile $file, $user): Material
    {
        return DB::transaction(function () use ($module, $data, $file, $user) {
            $material = new Material();
            $material->title = $data['title'];
            $material->type = $data['type'];
            
            if ($file) {
                $path = $file->store('materials', 'local');
                $material->file_path = $path;
            } else {
                $material->file_path = $data['content'] ?? '';
            }
            
            $material->creator_id = $user->id;
            $material->save();

            ModuleItem::create([
                'module_id' => $module->id,
                'itemable_type' => Material::class,
                'itemable_id' => $material->id,
                'order' => 1
            ]);

            return $material;
        });
    }

    public function updateMaterial(Material $material, array $data): Material
    {
        return DB::transaction(function () use ($material, $data) {
            $material->update($data);
            return $material;
        });
    }

    public function deleteMaterial(Material $material): void
    {
        DB::transaction(function () use ($material) {
            $material->delete();
        });
    }
    public function reorderItems(Module $module, array $items): void
    {
        // DB logic to reorder module items based on the provided array
        DB::transaction(function () use ($module, $items) {
            foreach ($items as $index => $itemId) {
                ModuleItem::where('module_id', $module->id)
                          ->where('id', $itemId)
                          ->update(['order' => $index + 1]);
            }
        });
    }

    public function recordMaterialView(Material $material, $user): void
    {
        // DB logic to record that the user viewed this material
        // e.g., MaterialView::firstOrCreate(['material_id' => $material->id, 'user_id' => $user->id]);
    }

    public function downloadMaterial(Material $material)
    {
        if ($material->type === 'video_link' || str_starts_with($material->file_path, 'http')) {
            return redirect($material->file_path);
        }

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($material->file_path)) {
            return response()->json(['message' => 'Archivo protegido no encontrado'], 404);
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->download($material->file_path, $material->title);
    }
}
