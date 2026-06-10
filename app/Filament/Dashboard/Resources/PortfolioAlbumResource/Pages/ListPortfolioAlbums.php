<?php

namespace App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages;

use App\Filament\Dashboard\Resources\PortfolioAlbumResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioAlbums extends ListRecords
{
    protected static string $resource = PortfolioAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
