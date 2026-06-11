<?php

namespace App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers;

use App\Models\PortfolioAlbum;
use App\Services\PortfolioService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Fotografije u albumu';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof PortfolioAlbum
            && $ownerRecord->photographer_profile_id === auth()->user()?->photographerProfile?->id;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image_path')
                ->label('Fotografija')
                ->webp('portfolio', 1600)
                ->imageEditor()
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Fotografija')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dodano')
                    ->dateTime('d.m.Y. H:i'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\Action::make('uploadImages')
                    ->label('Dodaj više fotografija')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('image_paths')
                            ->label('Fotografije')
                            ->webp('portfolio', 1600)
                            ->multiple()
                            ->reorderable()
                            ->maxParallelUploads(5)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var PortfolioAlbum $album */
                        $album = $this->getOwnerRecord();
                        abort_unless($album->photographer_profile_id === auth()->user()?->photographerProfile?->id, 403);

                        app(PortfolioService::class)->addImages(
                            $album->photographerProfile,
                            $album->category,
                            $data['image_paths'],
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Album je prazan')
            ->emptyStateDescription('Dodajte jednu ili više fotografija u ovaj album.');
    }
}
