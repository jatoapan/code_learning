<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourseEnrollmentController extends Controller
{
    public function enroll(Request $request, $id)
    {
        return response()->json(['message' => 'Enrolled in course'], 201);
    }

    public function drop($id)
    {
        return response()->json(['message' => 'Dropped course']);
    }

    public function enrollManual(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
        ]);

        return response()->json(['message' => 'User manually enrolled', 'data' => $validated], 201);
    }
}
