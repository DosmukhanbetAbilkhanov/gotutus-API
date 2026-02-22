<?php

declare(strict_types=1);

namespace App\Enums;

enum HangoutRequestStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Matched = 'matched';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
