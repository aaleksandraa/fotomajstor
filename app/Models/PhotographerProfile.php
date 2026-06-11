<?php

namespace App\Models;

use App\Enums\ProfileType;
use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PhotographerProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'profile_type' => ProfileType::class,
        'service_type' => ServiceType::class,
        'verified' => 'boolean',
        'active' => 'boolean',
        'featured' => 'boolean',
        'experience_years' => 'integer',
        'profile_views' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'primary_country_id');
    }

    public function primaryCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'primary_city_id');
    }

    public function socialLinks(): HasOne
    {
        return $this->hasOne(PhotographerSocialLink::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'photographer_category');
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'photographer_city');
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'photographer_country');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'photographer_location');
    }

    public function albums(): HasMany
    {
        return $this->hasMany(PortfolioAlbum::class);
    }

    public function portfolioImages(): HasManyThrough
    {
        return $this->hasManyThrough(PortfolioImage::class, PortfolioAlbum::class);
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(UnavailableDate::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(PhotographerBlogPost::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProfileView::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeRanked(Builder $query): Builder
    {
        return $query
            ->orderByDesc('featured')
            ->orderByDesc('verified')
            ->orderByDesc('profile_views')
            ->orderByDesc('created_at');
    }

    /**
     * Apply the public search filters described in PRD section 18.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeSearch(Builder $query, array $filters): Builder
    {
        $query->active();

        if (! empty($filters['country'])) {
            $country = $filters['country'];
            $query->where(function (Builder $q) use ($country) {
                $q->whereHas('countries', fn (Builder $sub) => $sub->where('countries.slug', $country))
                    ->orWhereHas('cities.country', fn (Builder $sub) => $sub->where('countries.slug', $country));
            });
        }

        if (! empty($filters['city'])) {
            $query->whereHas('cities', fn (Builder $q) => $q->where('cities.slug', $filters['city']));
        }

        if (! empty($filters['location'])) {
            $query->whereHas('locations', fn (Builder $q) => $q->where('locations.id', $filters['location']));
        }

        if (! empty($filters['category'])) {
            $query->whereHas('categories', fn (Builder $q) => $q->where('categories.slug', $filters['category']));
        }

        if (! empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (! empty($filters['profile_type'])) {
            $query->where('profile_type', $filters['profile_type']);
        }

        if (! empty($filters['date'])) {
            $query->whereDoesntHave('unavailableDates', fn (Builder $q) => $q->whereDate('date', $filters['date']));
        }

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('display_name', 'like', $term)
                    ->orWhere('company_name', 'like', $term)
                    ->orWhereHas('cities', fn (Builder $sub) => $sub->where('cities.name', 'like', $term));
            });
        }

        return $query;
    }

    public function isAvailableOn(string $date): bool
    {
        return ! $this->unavailableDates()->whereDate('date', $date)->exists();
    }

    public function contactName(): string
    {
        return $this->display_name;
    }
}
