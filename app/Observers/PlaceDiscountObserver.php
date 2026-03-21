<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendDiscountNotifications;
use App\Models\PlaceDiscount;

class PlaceDiscountObserver
{
    public function created(PlaceDiscount $discount): void
    {
        if ($discount->is_active) {
            SendDiscountNotifications::dispatch($discount);
        }
    }
}
