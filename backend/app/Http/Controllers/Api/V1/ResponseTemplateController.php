<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponseTemplateController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of response templates']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        return response()->json(['message' => 'Response template created successfully', 'data' => $validated], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Response template details', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        return response()->json(['message' => 'Response template updated successfully', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Response template deleted successfully']);
    }
}
