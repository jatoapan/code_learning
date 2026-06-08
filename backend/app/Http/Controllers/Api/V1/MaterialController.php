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
            'type' => 'required|string|in:pdf,video_link,ppt,pptx',
            'content' => 'nullable|string', // URL o texto
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,mp4,avi,mkv|max:51200', // Hasta 50MB
            'estimated_minutes' => 'nullable|integer',
            'is_required' => 'nullable|boolean',
        ]);

        $module = Module::findOrFail($id);

        $material = new Material();
        $material->title = $validated['title'];
        $material->type = $validated['type'];
        
        // Logica Hibrida: Si envian el archivo pesado, lo guardamos. Si envian un link, usamos el link.
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('materials', 'public');
            $material->file_path = '/storage/' . $path;
        } else {
            $material->file_path = $validated['content'] ?? '';
        }
        
        $material->creator_id = $request->user()->id;
        $material->save();

        // Enlazar usando la tabla polimorfica
        \App\Models\ModuleItem::create([
            'module_id' => $module->id,
            'itemable_type' => Material::class,
            'itemable_id' => $material->id,
            'order' => 1
        ]);

        return response()->json(['message' => 'Material created successfully', 'data' => $material], 201);
    }

    public function update(Request $request, $id)
    {
        $material = Material::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:pdf,video_link,ppt,pptx',
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
