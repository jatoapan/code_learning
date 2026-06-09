<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;

class ReportController extends Controller
{
    public function __construct() {
        $this->middleware('role:admin|moderator')->except(['store']);
    }

    public function index(Request $request) {
        return response()->json(['data' => Report::with('reporter:id,name', 'reportable')->orderBy('created_at', 'desc')->paginate(20)]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'reportable_type' => 'required|string',
            'reportable_id' => 'required|string',
            'reason' => 'required|string|in:spam,plagiarism,offensive_language,academic_dishonesty,other',
            'details' => 'nullable|string',
        ]);

        $report = new Report();
        $report->reporter_id = $request->user()->id;
        $report->reportable_type = $validated['reportable_type'];
        $report->reportable_id = $validated['reportable_id'];
        $report->reason = $validated['reason'];
        $report->details = $validated['details'] ?? null;
        $report->status = ReportStatus::Pending->value;
        $report->save();

        return response()->json(['message' => 'Report submitted', 'data' => $report], 201);
    }

    public function resolve(Request $request, $id) {
        $report = Report::findOrFail($id);
        $report->status = ReportStatus::Resolved->value;
        $report->resolved_by = $request->user()->id;
        $report->resolved_at = now();
        $report->save();
        return response()->json(['message' => 'Report resolved', 'data' => $report]);
    }

    public function escalate(Request $request, $id) {
        $report = Report::findOrFail($id);
        $report->status = ReportStatus::Escalated->value;
        $report->save();
        return response()->json(['message' => 'Report escalated', 'data' => $report]);
    }
}
