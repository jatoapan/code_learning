<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of reports']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        return response()->json(['message' => 'Report created successfully', 'data' => $validated], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Report details', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        return response()->json(['message' => 'Report updated successfully', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Report deleted successfully']);
    }
}
