<?php

namespace App\Notifications;

use App\Models\ProfessorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ApplicationReviewedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public ProfessorApplication $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $status  = $this->application->status;
        $message = $status === 'approved'
            ? '¡Tu solicitud para ser profesor fue aprobada! Ya tienes acceso de profesor.'
            : "Tu solicitud para ser profesor fue rechazada. Motivo: {$this->application->reviewer_comment}";

        return [
            'type'           => 'application_reviewed',
            'message'        => $message,
            'status'         => $status,
            'application_id' => $this->application->id,
            'comment'        => $this->application->reviewer_comment,
        ];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("App.Models.User.{$this->application->applicant_id}")];
    }

    public function broadcastAs(): string
    {
        return 'application.reviewed';
    }
}
