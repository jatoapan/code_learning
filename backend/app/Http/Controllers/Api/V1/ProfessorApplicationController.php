<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProfessorApplication;
use App\Notifications\ApplicationReviewedNotification;
use Illuminate\Http\Request;
use App\Enums\ProfessorApplicationStatus;

class ProfessorApplicationController extends Controller
{
    public function __construct(private \App\Services\ProfessorApplicationService $applicationService) {}

    public function index() {
        return response()->json(['data' => ProfessorApplication::with('applicant:id,name', 'reviewer:id,name')->paginate(15)]);
    }

    public function mine(Request $request) {
        return response()->json(['data' => ProfessorApplication::where('applicant_id', $request->user()->id)->get()]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'motivation'     => 'required|string',
            'qualifications' => 'required|string',
        ]);

        $app = new ProfessorApplication();
        $app->applicant_id  = $request->user()->id;
        $app->motivation    = $validated['motivation'];
        $app->qualifications = $validated['qualifications'];
        $app->status        = ProfessorApplicationStatus::Pending->value;
        $app->save();

        return response()->json(['message' => 'Application submitted', 'data' => $app], 201);
    }

    public function assignReviewer(Request $request, $id) {
        $app = ProfessorApplication::findOrFail($id);
        
        abort_unless($request->user()->hasRole('admin'), 403, 'Unauthorized.');

        $app->reviewer_id = $request->user()->id;
        $app->status      = ProfessorApplicationStatus::UnderReview->value;
        $app->save();
        return response()->json(['message' => 'Application assigned', 'data' => $app]);
    }

    public function review(Request $request, $id) {
        $app = ProfessorApplication::findOrFail($id);
        
        abort_unless($request->user()->hasRole('admin') || $request->user()->id === $app->reviewer_id, 403, 'Unauthorized.');

        $validated = $request->validate([
            'status'           => 'required|in:approved,rejected',
            'reviewer_comment' => 'nullable|string',
        ]);

        $app = $this->applicationService->reviewApplication($app, $validated);

        return response()->json(['message' => 'Application reviewed', 'data' => $app]);
    }
}
