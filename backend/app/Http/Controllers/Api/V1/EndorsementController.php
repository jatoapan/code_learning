<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\ForumPost;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    public function endorseMaterial($id)
    {
        return response()->json(['message' => 'Material endorsed successfully'], 200);
    }

    public function revokeMaterialEndorsement($id)
    {
        return response()->json(['message' => 'Material endorsement revoked successfully'], 200);
    }

    public function endorseThread($id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->moderator_endorsed_at = now();
        $thread->save();
        return response()->json(['message' => 'Thread endorsed successfully', 'data' => $thread], 200);
    }

    public function revokeThreadEndorsement($id)
    {
        $thread = ForumThread::findOrFail($id);
        $thread->moderator_endorsed_at = null;
        $thread->save();
        return response()->json(['message' => 'Thread endorsement revoked successfully', 'data' => $thread], 200);
    }

    public function endorsePost($id)
    {
        $post = ForumPost::findOrFail($id);
        $post->moderator_endorsed_at = now();
        $post->save();
        return response()->json(['message' => 'Post endorsed successfully', 'data' => $post], 200);
    }

    public function revokePostEndorsement($id)
    {
        $post = ForumPost::findOrFail($id);
        $post->moderator_endorsed_at = null;
        $post->save();
        return response()->json(['message' => 'Post endorsement revoked successfully', 'data' => $post], 200);
    }
}
