<?php

namespace App\Support;

use App\Models\PortfolioImage;
use Illuminate\Support\Collection;

class AuthBackground
{
    /**
     * Nasumične slike iz portfolija aktivnih fotografa za pozadinu auth stranica.
     *
     * @return Collection<int, string>
     */
    public static function images(int $limit = 100): Collection
    {
        $paths = PortfolioImage::query()
            ->whereHas('album.photographerProfile', fn ($query) => $query->where('active', true))
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('image_path');

        if ($paths->isEmpty()) {
            $paths = PortfolioImage::query()
                ->inRandomOrder()
                ->limit($limit)
                ->pluck('image_path');
        }

        return $paths
            ->map(fn ($path) => media_url($path))
            ->filter()
            ->values();
    }
}
