<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case Completed = 'completed';
    case Dropped = 'dropped';
}
