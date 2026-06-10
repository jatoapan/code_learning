<?php

namespace App\Jobs;

use App\Events\ChallengeAttemptResult;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Enums\ChallengeAttemptStatus;
use App\Services\Judge0Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessChallengeAttempt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Intentos máximos si el Job falla (ej: Judge0 no responde)
     */
    public int $tries = 3;

    /**
     * Timeout máximo en segundos para la evaluación
     */
    public int $timeout = 90;

    public function __construct(
        public ChallengeAttempt $attempt,
        public Challenge $challenge,
        public array $data
    ) {}

    public function handle(Judge0Service $judge0): void
    {
        $passed    = 0;
        $total     = $this->challenge->testCases->count();
        $status    = ChallengeAttemptStatus::Accepted->value;
        $stdout    = '';
        $stderr    = '';
        $execTime  = 0.0;
        $execMemory = 0;

        foreach ($this->challenge->testCases as $testCase) {
            $result = $judge0->submitCode(
                $this->data['language_id'],
                $this->data['submitted_code'],
                $testCase->expected_output,
                $testCase->input
            );

            if (isset($result['error'])) {
                $status = ChallengeAttemptStatus::CompileError->value;
                $stderr = $result['error'];
                break;
            }

            $judgeStatus = $result['status']['id'] ?? 0;
            $execTime   += (float) ($result['time'] ?? 0);
            $execMemory += (int)   ($result['memory'] ?? 0);

            if ($judgeStatus === 3) {
                $passed++;
            } elseif ($judgeStatus === 4) {
                $status = ChallengeAttemptStatus::WrongAnswer->value;
            } elseif ($judgeStatus === 5) {
                $status = ChallengeAttemptStatus::TimeLimitExceeded->value;
            } elseif ($judgeStatus === 6) {
                $status = ChallengeAttemptStatus::CompileError->value;
                $stderr = $result['compile_output'] ?? '';
                break;
            } else {
                $status = ChallengeAttemptStatus::RuntimeError->value;
                $stderr = $result['stderr'] ?? 'Runtime Error';
                break;
            }

            $stdout .= ($result['stdout'] ?? '') . "\n";
        }

        if ($passed < $total && $status === ChallengeAttemptStatus::Accepted->value) {
            $status = ChallengeAttemptStatus::WrongAnswer->value;
        }

        $points = ($status === ChallengeAttemptStatus::Accepted->value) ? $this->challenge->points : 0;

        // Actualizar el attempt con el resultado final
        $this->attempt->update([
            'status'              => $status,
            'test_cases_passed'   => $passed,
            'test_cases_total'    => $total,
            'points_awarded'      => $points,
            'execution_time_ms'   => $execTime * 1000,
            'execution_memory_kb' => $execMemory,
            'stdout'              => $stdout,
            'stderr'              => $stderr,
        ]);

        // Empujar resultado al Frontend vía Reverb WebSocket
        broadcast(new ChallengeAttemptResult($this->attempt->fresh()));
    }

    /**
     * Si el Job falla después de todos los intentos, marcar el attempt como error
     */
    public function failed(\Throwable $exception): void
    {
        $this->attempt->update([
            'status' => ChallengeAttemptStatus::RuntimeError->value,
            'stderr' => 'Error interno al procesar el intento: ' . $exception->getMessage(),
        ]);

        broadcast(new ChallengeAttemptResult($this->attempt->fresh()));
    }
}
