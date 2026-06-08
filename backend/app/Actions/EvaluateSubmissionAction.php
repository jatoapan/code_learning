<?php

namespace App\Actions;

use App\Models\ChallengeAttempt;
use App\Models\CourseUser;
use Illuminate\Support\Facades\DB;

class EvaluateSubmissionAction
{
    /**
     * Procesa el resultado asíncrono de Judge0 y otorga XP transaccionalmente.
     */
    public function execute(ChallengeAttempt $attempt, array $judge0Result): void
    {
        DB::transaction(function () use ($attempt, $judge0Result) {
            // Pessimistic Locking: Bloquea la fila del intento
            $attempt = ChallengeAttempt::where('id', $attempt->id)->lockForUpdate()->first();
            
            if ($attempt->status !== 'pending') {
                return; // Evitar doble evaluación
            }

            $verdict = $judge0Result['status']; // e.g. 'accepted', 'wrong_answer', 'compile_error'
            $isAccepted = $verdict === 'accepted';
            
            $attempt->status = $verdict;
            $attempt->execution_time_ms = $judge0Result['time'] ?? null;
            $attempt->execution_memory_kb = $judge0Result['memory'] ?? null;
            $attempt->test_cases_passed = $judge0Result['passed'] ?? 0;
            $attempt->test_cases_total = $judge0Result['total'] ?? 0;
            
            if ($isAccepted) {
                // Verificar si ya resolvió este reto exitosamente antes
                $alreadySolved = ChallengeAttempt::where('user_id', $attempt->user_id)
                    ->where('challenge_id', $attempt->challenge_id)
                    ->where('status', 'accepted')
                    ->where('id', '!=', $attempt->id)
                    ->exists();
                
                if (!$alreadySolved) {
                    $challengePoints = $attempt->challenge->points;
                    $attempt->points_awarded = $challengePoints;
                    
                    // Aumentar XP global
                    $user = $attempt->user;
                    if ($user) {
                        $user->increment('xp', $challengePoints);
                        
                        // Aumentar XP local del curso (Leaderboard local)
                        if ($attempt->challenge && $attempt->challenge->module) {
                            $courseId = $attempt->challenge->module->course_id;
                            $courseUser = CourseUser::where('user_id', $user->id)
                                ->where('course_id', $courseId)
                                ->lockForUpdate()
                                ->first();
                                
                            if ($courseUser) {
                                $courseUser->increment('xp', $challengePoints);
                            }
                        }
                    }
                }
            }

            $attempt->save();

            // Aquí se dispararía el evento WebSocket hacia Laravel Reverb
            // event(new \App\Events\ChallengeAttemptEvaluated($attempt));
        });
    }
}
