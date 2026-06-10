<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnavailableDate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function photographerProfile(): BelongsTo
    {
        return $this->belongsTo(PhotographerProfile::class);
    }
}
