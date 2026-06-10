<?php

namespace App\Models;

use App\Support\CatalogLocalization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getNameAttribute(?string $value): string
    {
        return CatalogLocalization::name('categories', $this->attributes['slug'] ?? '', $value ?? '');
    }

    public function getDescriptionAttribute(?string $value): ?string
    {
        if (! $value || app()->getLocale() === config('locales.default', 'bs')) {
            return $value;
        }

        return __('Profesionalci specijalizovani za :category.', ['category' => $this->name]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function photographers(): BelongsToMany
    {
        return $this->belongsToMany(PhotographerProfile::class, 'photographer_category');
    }

    public function portfolioAlbums(): HasMany
    {
        return $this->hasMany(PortfolioAlbum::class);
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(CategoryAlias::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
