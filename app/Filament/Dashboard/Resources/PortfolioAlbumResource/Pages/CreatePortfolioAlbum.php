<?php

namespace App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages;

use App\Filament\Dashboard\Resources\PortfolioAlbumResource;
use App\Models\Category;
use App\Services\PortfolioService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePortfolioAlbum extends CreateRecord
{
    protected static string $resource = PortfolioAlbumResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $profile = auth()->user()->photographerProfile;
        $category = Category::active()->findOrFail($data['category_id']);

        return app(PortfolioService::class)->addImages($profile, $category, $data['image_paths']);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
