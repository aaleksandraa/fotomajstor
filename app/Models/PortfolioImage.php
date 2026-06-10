<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioImage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (PortfolioImage $image) {
            if (blank($image->alt_text)) {
                $image->alt_text = $image->generateAltText();
            }
        });
    }

    public function generateAltText(): string
    {
        $album = $this->album;
        $profile = $album?->photographerProfile;
        $parts = array_filter([
            $album?->category?->name ?? $album?->title,
            $profile?->primaryCity?->name,
            $profile?->display_name,
        ]);

        return $parts ? implode(' - ', $parts) : 'Fotografija iz portfolija';
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(PortfolioAlbum::class, 'portfolio_album_id');
    }
}
