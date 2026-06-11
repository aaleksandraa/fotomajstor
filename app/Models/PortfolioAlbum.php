<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PortfolioAlbum extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function photographerProfile(): BelongsTo
    {
        return $this->belongsTo(PhotographerProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(PortfolioVideo::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public static function forProfileCategory(PhotographerProfile $profile, Category $category): self
    {
        $existing = static::query()
            ->where('photographer_profile_id', $profile->id)
            ->where('category_id', $category->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $baseSlug = Str::slug($category->name) ?: 'portfolio';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()->where('photographer_profile_id', $profile->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return static::create([
            'photographer_profile_id' => $profile->id,
            'category_id' => $category->id,
            'title' => $category->name,
            'slug' => $slug,
            'active' => true,
        ]);
    }

    public function deleteIfEmpty(): void
    {
        if (! $this->images()->exists() && ! $this->videos()->exists()) {
            $this->delete();
        }
    }
}
