<?php

namespace App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages;

use App\Filament\Dashboard\Resources\PortfolioAlbumResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioAlbum extends EditRecord
{
    protected static string $resource = PortfolioAlbumResource::class;

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
