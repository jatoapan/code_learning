<?php

namespace App\Services;

use App\Models\ForumThread;
use App\Models\ForumPost;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use App\Models\User;
use App\Enums\ThreadStatus;
use App\Enums\PostStatus;
use App\Notifications\NewForumReplyNotification;
use App\Notifications\PostAcceptedNotification;
use Illuminate\Support\Facades\DB;

class ForumService
{
    public function getThreadsByCourse($courseId)
    {
        return ForumThread::where('forumable_type', Course::class)
            ->where('forumable_id', $courseId)
            ->with('user:id,name,avatar_path')
            ->paginate(15);
    }

    public function getThreadsByModule($moduleId)
    {
        return ForumThread::where('forumable_type', Module::class)
            ->where('forumable_id', $moduleId)
            ->with('user:id,name,avatar_path')
            ->paginate(15);
    }

    public function getThreadsByChallenge($challengeId)
    {
        return ForumThread::where('forumable_type', Challenge::class)
            ->where('forumable_id', $challengeId)
            ->with('user:id,name,avatar_path')
            ->paginate(15);
    }

    public function createCourseThread(array $data, $courseId, $userId)
    {
        $course = Course::findOrFail($courseId);
        return $this->createThread($data, Course::class, $course->id, $userId);
    }

    public function createModuleThread(array $data, $moduleId, $userId)
    {
        $module = Module::findOrFail($moduleId);
        return $this->createThread($data, Module::class, $module->id, $userId);
    }

    public function createChallengeThread(array $data, $challengeId, $userId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        return $this->createThread($data, Challenge::class, $challenge->id, $userId);
    }

    private function createThread(array $data, $forumableType, $forumableId, $userId)
    {
        $thread = new ForumThread();
        $thread->title = $data['title'];
        $thread->body = $data['body'];
        $thread->user_id = $userId;
        $thread->forumable_type = $forumableType;
        $thread->forumable_id = $forumableId;
        $thread->status = ThreadStatus::Open->value;
        $thread->save();

        return $thread;
    }

    public function getThread($id)
    {
        return ForumThread::with(['user:id,name', 'posts.user:id,name'])->findOrFail($id);
    }

    public function updateThread($id, array $data, $user)
    {
        $thread = ForumThread::findOrFail($id);
        
        if ($thread->user_id !== $user->id && !$user->hasRole('admin|moderator')) {
            abort(403, 'Unauthorized');
        }

        $thread->update($data);

        return $thread;
    }

    public function deleteThread($id, $user)
    {
        $thread = ForumThread::findOrFail($id);
        if ($thread->user_id !== $user->id && !$user->hasRole('admin|moderator')) {
            abort(403, 'Unauthorized');
        }
        $thread->delete();
    }

    public function togglePinThread($id, $user)
    {
        $thread = ForumThread::findOrFail($id);
        if (!$user->hasRole('admin|moderator|professor|ta')) {
            abort(403, 'Unauthorized');
        }
        $thread->is_pinned = !$thread->is_pinned;
        $thread->save();
        return $thread;
    }

    public function lockThread($id, $user)
    {
        $thread = ForumThread::findOrFail($id);
        if (!$user->hasRole('admin|moderator|professor|ta')) {
            abort(403, 'Unauthorized');
        }
        $thread->status = ThreadStatus::Locked->value;
        $thread->save();
        return $thread;
    }

    public function createPost(array $data, $threadId, $userId)
    {
        $thread = ForumThread::findOrFail($threadId);

        $post = new ForumPost();
        $post->body = $data['body'];
        $post->user_id = $userId;
        $post->thread_id = $thread->id;
        $post->status = PostStatus::Visible->value;
        $post->save();

        // Notificar al autor del hilo si no es el mismo que responde
        if ($thread->user_id && $thread->user_id !== $userId) {
            $threadAuthor = User::find($thread->user_id);
            $replier     = User::find($userId);
            if ($threadAuthor && $replier) {
                $threadAuthor->notify(new NewForumReplyNotification($thread, $post, $replier->name));
            }
        }

        return $post;
    }

    public function updatePost($id, array $data, $user)
    {
        $post = ForumPost::findOrFail($id);
        
        if ($post->user_id !== $user->id && !$user->hasRole('admin|moderator')) {
            abort(403, 'Unauthorized');
        }

        $post->update($data);

        return $post;
    }

    public function deletePost($id, $user)
    {
        $post = ForumPost::findOrFail($id);
        if ($post->user_id !== $user->id && !$user->hasRole('admin|moderator')) {
            abort(403, 'Unauthorized');
        }
        $post->delete();
    }

    public function acceptAnswer($postId, $user)
    {
        return DB::transaction(function () use ($postId, $user) {
            $post   = ForumPost::findOrFail($postId);
            $thread = $post->thread;

            if ($thread->user_id !== $user->id) {
                abort(403, 'Unauthorized');
            }

            $post->is_accepted_answer = true;
            $post->save();

            $thread->status = ThreadStatus::Resolved->value;
            $thread->save();

            // Notificar al autor del post aceptado si no es el mismo que acepta
            if ($post->user_id && $post->user_id !== $user->id) {
                $postAuthor = User::find($post->user_id);
                if ($postAuthor) {
                    $postAuthor->notify(new PostAcceptedNotification($post, $thread));
                }
            }

            return $post;
        });
    }
}
