<?php

namespace App\Enums;

enum CourseRole: string
{
    case Student = 'student';
    case Professor = 'professor';
    case Ta = 'ta';
}
