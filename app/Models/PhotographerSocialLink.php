<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerSocialLink extends Model
{
    protected $guarded = [];

    public function photographerProfile(): BelongsTo
    {
        return $this->belongsTo(PhotographerProfile::class);
    }
}
