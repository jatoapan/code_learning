<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index() { return response()->json(['message' => 'List of system settings']); }
    public function store(Request $request) {
        $validated = $request->validate(['key' => 'required|string|max:255|unique:system_settings', 'value' => 'required|string']);
        return response()->json(['message' => 'System setting created successfully', 'data' => $validated], 201);
    }
    public function show($id) { return response()->json(['message' => 'System setting details', 'id' => $id]); }
    public function update(Request $request, $id) {
        $validated = $request->validate(['key' => 'sometimes|required|string|max:255', 'value' => 'sometimes|required|string']);
        return response()->json(['message' => 'System setting updated successfully', 'data' => $validated]);
    }
    public function destroy($id) { return response()->json(['message' => 'System setting deleted successfully']); }
}
