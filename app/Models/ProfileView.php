<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileView extends Model
{
    protected $guarded = [];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function photographerProfile(): BelongsTo
    {
        return $this->belongsTo(PhotographerProfile::class);
    }
}
