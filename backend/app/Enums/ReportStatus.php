<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Escalated = 'escalated';
    case Dismissed = 'dismissed';
}
