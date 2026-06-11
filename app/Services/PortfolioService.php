<?php

namespace App\Services;

use App\Models\Category;
use App\Models\PhotographerProfile;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioImage;
use Illuminate\Support\Facades\DB;

class PortfolioService
{
    /**
     * @param  array<int, string>  $imagePaths
     */
    public function addImages(PhotographerProfile $profile, Category $category, array $imagePaths): PortfolioAlbum
    {
        return DB::transaction(function () use ($profile, $category, $imagePaths): PortfolioAlbum {
            $album = PortfolioAlbum::forProfileCategory($profile, $category);
            $profile->categories()->syncWithoutDetaching([$category->id]);
            $nextSortOrder = ((int) $album->images()->max('sort_order')) + 1;

            foreach ($imagePaths as $imagePath) {
                $album->images()->create([
                    'image_path' => $imagePath,
                    'sort_order' => $nextSortOrder++,
                ]);
            }

            return $album;
        });
    }

    public function addImage(PhotographerProfile $profile, Category $category, string $imagePath): PortfolioImage
    {
        $album = $this->addImages($profile, $category, [$imagePath]);

        return $album->images()->latest('id')->firstOrFail();
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
