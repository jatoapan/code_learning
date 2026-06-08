<?php

namespace App\Enums;

enum ReportReason: string
{
    case Spam = 'spam';
    case Plagiarism = 'plagiarism';
    case OffensiveLanguage = 'offensive_language';
    case AcademicDishonesty = 'academic_dishonesty';
    case Other = 'other';
}
