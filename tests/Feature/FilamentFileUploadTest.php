<?php

namespace Tests\Feature;

use App\Support\FilamentWebpUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilamentFileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_webp_upload_meta_uses_relative_storage_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/test.webp', 'fake-webp');

        $field = FileUpload::make('image')->disk('public');

        $meta = FilamentWebpUpload::uploadedFileMeta($field, 'categories/test.webp', null);

        $this->assertNotNull($meta);
        $this->assertSame('/storage/categories/test.webp', $meta['url']);
    }

    public function test_webp_upload_meta_supports_remote_urls(): void
    {
        $field = FileUpload::make('image')->disk('public');

        $meta = FilamentWebpUpload::uploadedFileMeta(
            $field,
            'https://picsum.photos/seed/test/800/600',
            null,
        );

        $this->assertSame('https://picsum.photos/seed/test/800/600', $meta['url']);
    }

    public function test_image_service_converts_large_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('large.jpg', 3000, 2000);

        $path = FilamentWebpUpload::saveAsWebp($file, 'portfolio', 1600);

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
    }
}
