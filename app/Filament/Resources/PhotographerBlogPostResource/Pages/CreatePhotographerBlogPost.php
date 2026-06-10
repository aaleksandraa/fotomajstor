<?php

namespace App\Filament\Resources\PhotographerBlogPostResource\Pages;

use App\Filament\Resources\PhotographerBlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePhotographerBlogPost extends CreateRecord
{
    protected static string $resource = PhotographerBlogPostResource::class;
}
