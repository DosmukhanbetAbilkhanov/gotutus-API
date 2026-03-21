<?php

declare(strict_types=1);

namespace App\Enums;

enum BillSplit: string
{
    case SplitEven = 'split_even';
    case PayOwn = 'pay_own';
    case OrganizerPays = 'organizer_pays';
}
