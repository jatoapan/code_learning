<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = Report::with('reporter:id,name', 'reportable')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);
                         
        return response()->json(['data' => $reports]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reportable_type' => 'required|string',
            'reportable_id' => 'required|string',
            'reason' => 'required|string|in:spam,plagiarism,offensive_language,academic_dishonesty,other',
            'description' => 'nullable|string',
        ]);

        $report = new Report();
        $report->reporter_id = $request->user()->id;
        $report->reportable_type = $validated['reportable_type'];
        $report->reportable_id = $validated['reportable_id'];
        $report->reason = $validated['reason'];
        $report->description = $validated['description'];
        $report->status = ReportStatus::Pending->value;
        $report->save();

        return response()->json(['message' => 'Report submitted successfully', 'data' => $report], 201);
    }

    public function resolve(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $report->status = ReportStatus::Resolved->value;
        $report->resolver_id = $request->user()->id;
        $report->resolved_at = now();
        $report->save();

        return response()->json(['message' => 'Report resolved', 'data' => $report]);
    }

    public function escalate(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $report->status = ReportStatus::Escalated->value;
        $report->save();

        return response()->json(['message' => 'Report escalated', 'data' => $report]);
    }
}
