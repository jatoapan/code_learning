<?php

namespace App\Models;

use App\Enums\ChallengeAttemptStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'submitted_code',
        'language_id',
        'status',
        'test_cases_passed',
        'test_cases_total',
        'points_awarded',
        'execution_time_ms',
        'execution_memory_kb',
        'stdout',
        'stderr',
        'feedback',
    ];

    protected $casts = [
        'status' => ChallengeAttemptStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
