<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportUserController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of support tickets']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        return response()->json(['message' => 'Support ticket created successfully', 'data' => $validated], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Support ticket details', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string',
        ]);

        return response()->json(['message' => 'Support ticket updated successfully', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Support ticket deleted successfully']);
    }
}
