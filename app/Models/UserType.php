<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    public const SLUG_CLIENT = 'client';

    public const SLUG_ADMIN = 'admin';

    public const SLUG_CITY_MANAGER = 'city_manager';

    protected $fillable = [
        'slug',
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
