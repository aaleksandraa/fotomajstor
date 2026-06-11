<?php

namespace App\Filament\Dashboard\Resources\PortfolioImageResource\Pages;

use App\Filament\Dashboard\Resources\PortfolioImageResource;
use App\Models\Category;
use App\Services\PortfolioService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePortfolioImage extends CreateRecord
{
    protected static string $resource = PortfolioImageResource::class;

    protected static bool $canCreateAnother = true;

    protected function handleRecordCreation(array $data): Model
    {
        $profile = auth()->user()->photographerProfile;
        $category = Category::active()->findOrFail($data['category_id']);

        return app(PortfolioService::class)->addImage($profile, $category, $data['image_path']);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
