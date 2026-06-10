<?php

namespace App\Filament\Resources\PortfolioAlbumResource\Pages;

use App\Filament\Resources\PortfolioAlbumResource;
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
