<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProfessorApplication;
use App\Notifications\ApplicationReviewedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Enums\ProfessorApplicationStatus;

class ProfessorApplicationController extends Controller
{
    public function __construct(private \App\Services\ProfessorApplicationService $applicationService) {}

    public function index() {
        Gate::authorize('viewAny', ProfessorApplication::class);
        return response()->json(['data' => $this->applicationService->getPaginatedApplications()]);
    }

    public function mine(Request $request) {
        return response()->json(['data' => $this->applicationService->getUserApplications($request->user()->id)]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'motivation'     => 'required|string',
            'qualifications' => 'required|string',
        ]);

        return response()->json([
            'message' => 'Application submitted', 
            'data' => $this->applicationService->createApplication($validated, $request->user())
        ], 201);
    }

    public function assignReviewer(Request $request, $id) {
        $app = clone $this->applicationService->getApplication($id);
        Gate::authorize('manage', ProfessorApplication::class);

        $app = $this->applicationService->assignReviewer($app, $request->user());
        return response()->json(['message' => 'Application assigned', 'data' => $app]);
    }

    public function review(Request $request, $id) {
        $app = $this->applicationService->getApplication($id);
        Gate::authorize('review', $app);

        $validated = $request->validate([
            'status'           => 'required|in:approved,rejected',
            'reviewer_comment' => 'nullable|string',
        ]);

        return response()->json([
            'message' => 'Application reviewed', 
            'data' => $this->applicationService->reviewApplication($app, $validated)
        ]);
    }
}
