<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForumThreadController extends Controller
{
    public function indexByCourse($id)
    {
        return response()->json([
            'message' => 'Threads for course ' . $id
        ], 200);
    }

    public function indexByModule($id)
    {
        return response()->json([
            'message' => 'Threads for module ' . $id
        ], 200);
    }

    public function indexByChallenge($id)
    {
        return response()->json([
            'message' => 'Threads for challenge ' . $id
        ], 200);
    }

    public function storeForCourse(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        return response()->json([
            'message' => 'Thread created for course',
            'data' => $validated
        ], 201);
    }

    public function storeForModule(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        return response()->json([
            'message' => 'Thread created for module',
            'data' => $validated
        ], 201);
    }

    public function storeForChallenge(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        return response()->json([
            'message' => 'Thread created for challenge',
            'data' => $validated
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'message' => 'Thread details'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        return response()->json([
            'message' => 'Thread updated',
            'data' => $validated
        ], 200);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Thread deleted'
        ], 200);
    }

    public function togglePinned(Request $request, $id)
    {
        $validated = $request->validate([
            'is_pinned' => 'required|boolean',
        ]);

        return response()->json([
            'message' => 'Thread pin status updated',
            'data' => $validated
        ], 200);
    }

    public function changeStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,resolved,locked,hidden',
        ]);

        return response()->json([
            'message' => 'Thread status updated',
            'data' => $validated
        ], 200);
    }
}
