<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProfessorApplication;
use Illuminate\Http\Request;
use App\Enums\ProfessorApplicationStatus;

class ProfessorApplicationController extends Controller
{
    public function index()
    {
        $applications = ProfessorApplication::with('applicant:id,name', 'reviewer:id,name')->paginate(15);
        return response()->json(['data' => $applications]);
    }

    public function mine(Request $request)
    {
        $applications = ProfessorApplication::where('applicant_id', $request->user()->id)->get();
        return response()->json(['data' => $applications]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'motivation' => 'required|string',
            'qualifications' => 'required|string',
        ]);

        $app = new ProfessorApplication();
        $app->applicant_id = $request->user()->id;
        $app->motivation = $validated['motivation'];
        $app->qualifications = $validated['qualifications'];
        $app->status = ProfessorApplicationStatus::Pending->value;
        $app->save();

        return response()->json(['message' => 'Application submitted successfully', 'data' => $app], 201);
    }

    public function assign(Request $request, $id)
    {
        $app = ProfessorApplication::findOrFail($id);
        $app->reviewer_id = $request->user()->id;
        $app->status = ProfessorApplicationStatus::UnderReview->value;
        $app->save();

        return response()->json(['message' => 'Application assigned', 'data' => $app]);
    }

    public function review(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'reviewer_comment' => 'nullable|string',
        ]);

        $app = ProfessorApplication::findOrFail($id);
        $app->status = $validated['status'];
        $app->reviewer_comment = $validated['reviewer_comment'] ?? null;
        $app->reviewed_at = now();
        $app->save();

        if ($validated['status'] === 'approved') {
            $app->applicant->assignRole('professor');
        }

        return response()->json(['message' => 'Application reviewed', 'data' => $app]);
    }
}
