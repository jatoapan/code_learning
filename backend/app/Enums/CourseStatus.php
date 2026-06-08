<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Public = 'public';
    case Unlisted = 'unlisted';
}
