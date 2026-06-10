<?php

namespace App\Notifications;

use App\Models\ForumThread;
use App\Models\ForumPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewForumReplyNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public ForumThread $thread,
        public ForumPost $post,
        public string $replierName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'new_forum_reply',
            'message'     => "{$this->replierName} respondió tu hilo: \"{$this->thread->title}\"",
            'thread_id'   => $this->thread->id,
            'post_id'     => $this->post->id,
            'thread_title'=> $this->thread->title,
        ];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("App.Models.User.{$this->thread->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'new.forum.reply';
    }
}
