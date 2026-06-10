<?php

namespace App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages;

use App\Filament\Dashboard\Resources\PortfolioAlbumResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePortfolioAlbum extends CreateRecord
{
    protected static string $resource = PortfolioAlbumResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['photographer_profile_id'] = auth()->user()?->photographerProfile?->id;

        return $data;
    }
}
