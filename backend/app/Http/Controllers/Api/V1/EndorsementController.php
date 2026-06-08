<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\ForumPost;
use App\Models\Material;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    public function endorseMaterial(Request $request, $id)
    {
        $material = Material::findOrFail($id);
        $material->endorsements()->firstOrCreate([
            'user_id' => $request->user()->id ?? 1
        ]);
        return response()->json(['message' => 'Material endorsed successfully'], 200);
    }

    public function revokeMaterial(Request $request, $id)
    {
        $material = Material::findOrFail($id);
        $material->endorsements()->where('user_id', $request->user()->id ?? 1)->delete();
        return response()->json(['message' => 'Material endorsement revoked successfully'], 200);
    }

    public function endorseThread(Request $request, $id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->endorsements()->firstOrCreate([
            'user_id' => $request->user()->id ?? 1
        ]);
        return response()->json(['message' => 'Thread endorsed successfully'], 200);
    }

    public function revokeThread(Request $request, $id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->endorsements()->where('user_id', $request->user()->id ?? 1)->delete();
        return response()->json(['message' => 'Thread endorsement revoked successfully'], 200);
    }

    public function endorsePost(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        $post->endorsements()->firstOrCreate([
            'user_id' => $request->user()->id ?? 1
        ]);
        return response()->json(['message' => 'Post endorsed successfully'], 200);
    }

    public function revokePost(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        $post->endorsements()->where('user_id', $request->user()->id ?? 1)->delete();
        return response()->json(['message' => 'Post endorsement revoked successfully'], 200);
    }
}
