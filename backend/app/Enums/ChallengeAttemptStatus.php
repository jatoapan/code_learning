<?php

namespace App\Enums;

enum ChallengeAttemptStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case WrongAnswer = 'wrong_answer';
    case CompileError = 'compile_error';
    case RuntimeError = 'runtime_error';
    case TimeLimitExceeded = 'time_limit_exceeded';
}
