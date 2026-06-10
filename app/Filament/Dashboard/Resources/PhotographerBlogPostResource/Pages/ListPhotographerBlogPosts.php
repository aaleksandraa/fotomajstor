<?php

namespace App\Filament\Dashboard\Resources\PhotographerBlogPostResource\Pages;

use App\Filament\Dashboard\Resources\PhotographerBlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhotographerBlogPosts extends ListRecords
{
    protected static string $resource = PhotographerBlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
