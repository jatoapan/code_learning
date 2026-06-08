<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'List of notifications']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        return response()->json(['message' => 'Notification created successfully', 'data' => $validated], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Notification details', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'read_at' => 'nullable|date',
        ]);

        return response()->json(['message' => 'Notification updated successfully', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
