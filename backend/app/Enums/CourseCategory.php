<?php

namespace App\Enums;

enum CourseCategory: string
{
    case Programming = 'programming';
    case Web = 'web';
    case Mobile = 'mobile';
    case DataScience = 'data_science';
    case Devops = 'devops';
    case Design = 'design';
}
