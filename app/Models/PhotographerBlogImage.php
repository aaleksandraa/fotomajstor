<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerBlogImage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (PhotographerBlogImage $image) {
            if (blank($image->alt_text)) {
                $image->alt_text = $image->post?->title ?? 'Fotografija uz članak';
            }
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(PhotographerBlogPost::class, 'photographer_blog_post_id');
    }
}
