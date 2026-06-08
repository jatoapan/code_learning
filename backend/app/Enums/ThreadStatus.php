<?php

namespace App\Enums;

enum ThreadStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case Locked = 'locked';
    case Hidden = 'hidden';
}
