<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    public function endorseMaterial($id)
    {
        return response()->json([
            'message' => 'Material endorsed successfully'
        ], 200);
    }

    public function revokeMaterialEndorsement($id)
    {
        return response()->json([
            'message' => 'Material endorsement revoked successfully'
        ], 200);
    }

    public function endorseThread($id)
    {
        return response()->json([
            'message' => 'Thread endorsed successfully'
        ], 200);
    }

    public function revokeThreadEndorsement($id)
    {
        return response()->json([
            'message' => 'Thread endorsement revoked successfully'
        ], 200);
    }

    public function endorsePost($id)
    {
        return response()->json([
            'message' => 'Post endorsed successfully'
        ], 200);
    }

    public function revokePostEndorsement($id)
    {
        return response()->json([
            'message' => 'Post endorsement revoked successfully'
        ], 200);
    }
}
