<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfessorApplicationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['message' => 'List of applications']);
    }

    public function mine(Request $request)
    {
        return response()->json(['message' => 'My application status']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'motivation' => 'required|string',
            'qualifications' => 'nullable|string',
        ]);

        return response()->json(['message' => 'Application submitted', 'data' => $validated], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'reviewer_id' => 'nullable|string',
            'status' => 'sometimes|required|string|in:under_review,approved,rejected',
            'comment' => 'nullable|string',
        ]);

        return response()->json(['message' => 'Application updated', 'data' => $validated]);
    }
}
