<?php

namespace App\Filament\Resources\PhotographerBlogPostResource\Pages;

use App\Filament\Resources\PhotographerBlogPostResource;
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
