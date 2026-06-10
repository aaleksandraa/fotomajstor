<?php

namespace App\Models;

use App\Support\CatalogLocalization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getNameAttribute(?string $value): string
    {
        return CatalogLocalization::name('countries', $this->attributes['slug'] ?? '', $value ?? '');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function photographers(): BelongsToMany
    {
        return $this->belongsToMany(PhotographerProfile::class, 'photographer_country');
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
