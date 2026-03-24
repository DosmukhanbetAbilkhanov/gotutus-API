<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceComplaintStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';
}
