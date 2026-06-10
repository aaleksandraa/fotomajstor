<?php

namespace App\Filament\Resources\PortfolioAlbumResource\Pages;

use App\Filament\Resources\PortfolioAlbumResource;
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
}
