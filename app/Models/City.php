<?php

namespace App\Models;

use App\Support\CatalogLocalization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class City extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getNameAttribute(?string $value): string
    {
        return CatalogLocalization::name('cities', $this->attributes['slug'] ?? '', $value ?? '');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function photographers(): BelongsToMany
    {
        return $this->belongsToMany(PhotographerProfile::class, 'photographer_city');
    }

    public function location(): HasOne
    {
        return $this->hasOne(Location::class);
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
