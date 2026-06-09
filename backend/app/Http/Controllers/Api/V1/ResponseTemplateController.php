<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ResponseTemplate;
use Illuminate\Http\Request;

class ResponseTemplateController extends Controller
{
    public function index() { return response()->json(['data' => ResponseTemplate::paginate(20)]); }
    public function store(Request $request) {
        $validated = $request->validate(['title' => 'required|string|max:255', 'body' => 'required|string']);
        return response()->json(['message' => 'Response template created successfully', 'data' => ResponseTemplate::create($validated)], 201);
    }
    public function show($id) { return response()->json(['data' => ResponseTemplate::findOrFail($id)]); }
    public function update(Request $request, $id) {
        $validated = $request->validate(['title' => 'sometimes|required|string|max:255', 'body' => 'sometimes|required|string']);
        $template = ResponseTemplate::findOrFail($id);
        $template->update($validated);
        return response()->json(['message' => 'Response template updated successfully', 'data' => $template]);
    }
    public function destroy($id) {
        ResponseTemplate::findOrFail($id)->delete();
        return response()->json(['message' => 'Response template deleted successfully']);
    }
}
