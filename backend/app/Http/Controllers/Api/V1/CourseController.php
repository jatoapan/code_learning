<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['message' => 'List of courses']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image_path' => 'nullable|string|max:255',
            'status' => 'required|string|in:draft,public,unlisted',
            'has_leaderboard' => 'boolean',
        ]);

        return response()->json(['message' => 'Course created', 'data' => $validated], 201);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Course details']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'sometimes|required|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image_path' => 'nullable|string|max:255',
            'status' => 'sometimes|required|string|in:draft,public,unlisted',
            'has_leaderboard' => 'boolean',
        ]);

        return response()->json(['message' => 'Course updated', 'data' => $validated]);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Course deleted']);
    }

    public function progress($id)
    {
        return response()->json(['message' => 'Course progress']);
    }

    public function leaderboard($id)
    {
        return response()->json(['message' => 'Course leaderboard']);
    }

    public function stats($id)
    {
        return response()->json(['message' => 'Course statistics']);
    }

    public function addStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'role' => 'required|string',
        ]);

        return response()->json(['message' => 'Staff added', 'data' => $validated]);
    }

    public function removeStaff($id, $userId)
    {
        return response()->json(['message' => 'Staff removed']);
    }

    public function analytics($id)
    {
        return response()->json(['message' => 'Course analytics']);
    }
}
