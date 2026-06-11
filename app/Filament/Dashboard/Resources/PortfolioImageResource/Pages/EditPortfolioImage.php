<?php

namespace App\Filament\Dashboard\Resources\PortfolioImageResource\Pages;

use App\Filament\Dashboard\Resources\PortfolioImageResource;
use App\Models\Category;
use App\Services\PortfolioService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPortfolioImage extends EditRecord
{
    protected static string $resource = PortfolioImageResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['category_id'] = $this->record->album?->category_id;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $profile = auth()->user()->photographerProfile;
        $category = Category::active()->findOrFail($data['category_id']);

        return app(PortfolioService::class)->updateImage($record, $profile, $category, $data['image_path']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
