<?php

namespace App\Support;

use App\Services\ImageService;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FilamentWebpUpload
{
    /**
     * Metadata za FilePond preview — koristi relativne URL-ove (/storage/...)
     * da preview radi i kad APP_URL ne odgovara hostu (localhost vs 127.0.0.1).
     *
     * @return array{name: string, size: int, type: string|null, url: string}|null
     */
    public static function uploadedFileMeta(FileUpload $component, string $file, string | array | null $storedFileNames): ?array
    {
        if (Str::startsWith($file, ['http://', 'https://', '//'])) {
            return [
                'name' => basename(parse_url($file, PHP_URL_PATH) ?: 'image.jpg'),
                'size' => 0,
                'type' => 'image/jpeg',
                'url' => $file,
            ];
        }

        $storage = $component->getDisk();

        if (! $storage->exists($file)) {
            return null;
        }

        return [
            'name' => (is_array($storedFileNames) ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
            'size' => $storage->size($file),
            'type' => $storage->mimeType($file),
            'url' => self::publicAssetUrl($file),
        ];
    }

    public static function saveAsWebp(UploadedFile $file, string $directory, int $maxWidth): string
    {
        return app(ImageService::class)->storeWebp($file, $directory, $maxWidth);
    }

    public static function publicAssetUrl(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }
}
