<?php

namespace App\Models;

use App\Enums\PhotographerBlogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PhotographerBlogPost extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => PhotographerBlogStatus::class,
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (PhotographerBlogPost $post): void {
            if (filled($post->slug)) {
                return;
            }

            $baseSlug = Str::slug($post->title) ?: 'clanak';
            $slug = $baseSlug;
            $suffix = 2;

            while (static::query()
                ->where('photographer_profile_id', $post->photographer_profile_id)
                ->where('slug', $slug)
                ->when($post->exists, fn (Builder $query) => $query->whereKeyNot($post->getKey()))
                ->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $post->slug = $slug;
        });
    }

    public function photographerProfile(): BelongsTo
    {
        return $this->belongsTo(PhotographerProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PhotographerBlogImage::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PhotographerBlogStatus::Published->value)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
