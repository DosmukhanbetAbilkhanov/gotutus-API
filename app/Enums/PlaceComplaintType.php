<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceComplaintType: string
{
    case DiscountNotHonored = 'discount_not_honored';
    case AmenitiesNotProvided = 'amenities_not_provided';
    case Other = 'other';
}
