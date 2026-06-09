<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    public function __construct() {
        $this->middleware('role:admin');
    }

    public function index() { return response()->json(['message' => 'List of admin logs']); }
    public function store(Request $request) {
        $validated = $request->validate(['action' => 'required|string|max:255', 'details' => 'nullable|string']);
        return response()->json(['message' => 'Admin log created successfully', 'data' => $validated], 201);
    }
    public function show($id) { return response()->json(['message' => 'Admin log details', 'id' => $id]); }
    public function update(Request $request, $id) {
        $validated = $request->validate(['action' => 'sometimes|required|string|max:255', 'details' => 'nullable|string']);
        return response()->json(['message' => 'Admin log updated successfully', 'data' => $validated]);
    }
    public function destroy($id) { return response()->json(['message' => 'Admin log deleted successfully']); }
}
