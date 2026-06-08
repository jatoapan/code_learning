<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ResponseTemplate;
use Illuminate\Http\Request;

class ResponseTemplateController extends Controller
{
    public function index()
    {
        $templates = ResponseTemplate::paginate(20);
        return response()->json(['data' => $templates]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template = ResponseTemplate::create($validated);

        return response()->json(['message' => 'Response template created successfully', 'data' => $template], 201);
    }

    public function show($id)
    {
        $template = ResponseTemplate::findOrFail($id);
        return response()->json(['data' => $template]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $template = ResponseTemplate::findOrFail($id);
        $template->update($validated);

        return response()->json(['message' => 'Response template updated successfully', 'data' => $template]);
    }

    public function destroy($id)
    {
        $template = ResponseTemplate::findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Response template deleted successfully']);
    }
}
