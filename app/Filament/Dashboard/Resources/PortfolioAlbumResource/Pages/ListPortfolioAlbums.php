<?php

namespace App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages;

use App\Filament\Dashboard\Resources\PortfolioAlbumResource;
use App\Models\Category;
use App\Models\PortfolioVideo;
use App\Services\PortfolioService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioAlbums extends ListRecords
{
    protected static string $resource = PortfolioAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Novi album i fotografije'),
            Actions\Action::make('addVideo')
                ->label('Dodaj video')
                ->icon('heroicon-o-video-camera')
                ->form([
                    Forms\Components\Select::make('category_id')
                        ->label('Kategorija')
                        ->options(Category::active()->ordered()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false)
                        ->required(),
                    Forms\Components\TextInput::make('url')
                        ->label('YouTube ili Vimeo link')
                        ->url()
                        ->required()
                        ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                            if (PortfolioVideo::parseVideoUrl((string) $value) === null) {
                                $fail('Unesite ispravan YouTube ili Vimeo link.');
                            }
                        }),
                    Forms\Components\TextInput::make('title')
                        ->label('Naslov videa')
                        ->helperText('Opcionalno.')
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $profile = auth()->user()->photographerProfile;
                    $category = Category::active()->findOrFail($data['category_id']);

                    app(PortfolioService::class)->addVideo(
                        $profile,
                        $category,
                        $data['url'],
                        $data['title'] ?? null,
                    );

                    Notification::make()
                        ->success()
                        ->title('Video je dodat u portfolio')
                        ->body("Kategorija: {$category->name}")
                        ->send();
                }),
        ];
    }
}
