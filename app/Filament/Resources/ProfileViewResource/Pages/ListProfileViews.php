<?php

namespace App\Filament\Resources\ProfileViewResource\Pages;

use App\Filament\Resources\ProfileViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfileViews extends ListRecords
{
    protected static string $resource = ProfileViewResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
