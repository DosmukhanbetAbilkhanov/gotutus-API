<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'hangout_request_id',
        'reporter_user_id',
        'reported_user_id',
        'showed_up',
    ];

    protected function casts(): array
    {
        return [
            'showed_up' => 'boolean',
        ];
    }

    public function hangoutRequest(): BelongsTo
    {
        return $this->belongsTo(HangoutRequest::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
}
