<?php

namespace App\Notifications;

use App\Models\ForumPost;
use App\Models\ForumThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostAcceptedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public ForumPost $post,
        public ForumThread $thread
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'post_accepted',
            'message'     => "¡Tu respuesta fue aceptada como correcta en el hilo: \"{$this->thread->title}\"!",
            'thread_id'   => $this->thread->id,
            'post_id'     => $this->post->id,
            'thread_title'=> $this->thread->title,
        ];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("App.Models.User.{$this->post->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'post.accepted';
    }
}
