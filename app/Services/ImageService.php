<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Resize (if needed), convert an uploaded image to WebP and store it on the public disk.
     * Returns the stored relative path (e.g. "profiles/ab12....webp").
     */
    public function storeWebp(UploadedFile $file, string $directory = 'uploads', int $maxWidth = 1600, int $quality = 82): string
    {
        $manager = ImageManager::gd();

        $sourcePath = $file->getRealPath();

        if (! is_readable($sourcePath)) {
            throw new \RuntimeException('Upload fajl nije dostupan za obradu.');
        }

        $image = $manager->read($sourcePath);

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $encoded = $image->toWebp($quality);

        $path = trim($directory, '/').'/'.Str::random(40).'.webp';
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
