<?php

namespace App\Services;

use App\Models\Category;
use App\Models\PhotographerProfile;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioImage;
use Illuminate\Support\Facades\DB;

class PortfolioService
{
    public function addImage(PhotographerProfile $profile, Category $category, string $imagePath): PortfolioImage
    {
        return DB::transaction(function () use ($profile, $category, $imagePath): PortfolioImage {
            $album = PortfolioAlbum::forProfileCategory($profile, $category);
            $profile->categories()->syncWithoutDetaching([$category->id]);

            return PortfolioImage::create([
                'portfolio_album_id' => $album->id,
                'image_path' => $imagePath,
            ]);
        });
    }

    public function updateImage(PortfolioImage $image, PhotographerProfile $profile, Category $category, string $imagePath): PortfolioImage
    {
        return DB::transaction(function () use ($image, $profile, $category, $imagePath): PortfolioImage {
            $previousAlbum = $image->album;
            $album = PortfolioAlbum::forProfileCategory($profile, $category);
            $profile->categories()->syncWithoutDetaching([$category->id]);

            $image->update([
                'portfolio_album_id' => $album->id,
                'image_path' => $imagePath,
                'alt_text' => null,
            ]);

            if ($previousAlbum && ! $previousAlbum->is($album)) {
                $previousAlbum->deleteIfEmpty();
            }

            return $image;
        });
    }
}
