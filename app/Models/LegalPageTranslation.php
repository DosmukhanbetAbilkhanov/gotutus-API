<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalPageTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'legal_page_id',
        'language_code',
        'title',
        'content',
    ];

    public function legalPage(): BelongsTo
    {
        return $this->belongsTo(LegalPage::class);
    }
}
