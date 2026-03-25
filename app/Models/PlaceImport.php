<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceImport extends Model
{
    protected $fillable = [
        'city_id',
        'user_id',
        'file_name',
        'total_rows',
        'imported_count',
        'skipped_count',
        'failed_count',
        'errors',
        'warnings',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'warnings' => 'array',
            'total_rows' => 'integer',
            'imported_count' => 'integer',
            'skipped_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
