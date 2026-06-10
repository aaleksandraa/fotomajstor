<?php

namespace App\Filament\Resources\PhotographerBlogPostResource\Pages;

use App\Filament\Resources\PhotographerBlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhotographerBlogPost extends EditRecord
{
    protected static string $resource = PhotographerBlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
