<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Reviewed = 'reviewed';
    case ActionTaken = 'action_taken';
    case Dismissed = 'dismissed';
}
