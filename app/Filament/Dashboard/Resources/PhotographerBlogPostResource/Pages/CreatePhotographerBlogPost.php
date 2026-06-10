<?php

namespace App\Filament\Dashboard\Resources\PhotographerBlogPostResource\Pages;

use App\Filament\Dashboard\Resources\PhotographerBlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePhotographerBlogPost extends CreateRecord
{
    protected static string $resource = PhotographerBlogPostResource::class;

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
