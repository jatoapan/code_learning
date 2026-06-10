<?php

namespace App\Events;

use App\Models\ChallengeAttempt;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeAttemptResult implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChallengeAttempt $attempt) {}

    /**
     * Canal privado del usuario — solo él recibe su resultado
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("attempts.{$this->attempt->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'attempt.result';
    }

    public function broadcastWith(): array
    {
        return [
            'attempt_id'         => $this->attempt->id,
            'status'             => $this->attempt->status,
            'test_cases_passed'  => $this->attempt->test_cases_passed,
            'test_cases_total'   => $this->attempt->test_cases_total,
            'points_awarded'     => $this->attempt->points_awarded,
            'execution_time_ms'  => $this->attempt->execution_time_ms,
            'stdout'             => $this->attempt->stdout,
            'stderr'             => $this->attempt->stderr,
        ];
    }
}
