<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers\ImagesRelationManager;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers\VideosRelationManager;
use App\Models\Category;
use App\Models\PortfolioAlbum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PortfolioAlbumResource extends Resource
{
    protected static ?string $model = PortfolioAlbum::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Portfolio';

    protected static ?string $modelLabel = 'portfolio album';

    protected static ?string $pluralModelLabel = 'portfolio albumi';

    protected static ?int $navigationSort = 2;

    protected static function profileId(): ?int
    {
        return auth()->user()?->photographerProfile?->id;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('photographer_profile_id', static::profileId())
            ->with(['category', 'images', 'videos']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->label('Kategorija albuma')
                ->options(fn () => Category::active()
                    ->ordered()
                    ->when(
                        $form->getOperation() === 'create',
                        fn (Builder $query) => $query->whereDoesntHave(
                            'portfolioAlbums',
                            fn (Builder $albumQuery) => $albumQuery->where('photographer_profile_id', static::profileId()),
                        ),
                    )
                    ->pluck('name', 'id'))
                ->helperText('Jedna kategorija predstavlja jedan portfolio album.')
                ->searchable()
                ->required()
                ->disabledOn('edit')
                ->native(false),
            Forms\Components\FileUpload::make('image_paths')
                ->label('Fotografije')
                ->helperText('Odaberite više fotografija odjednom. Redoslijed kasnije mijenjate unutar albuma.')
                ->webp('portfolio', 1600)
                ->multiple()
                ->reorderable()
                ->maxParallelUploads(5)
                ->required()
                ->hiddenOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.image_path')
                    ->label('Fotografije')
                    ->disk('public')
                    ->stacked()
                    ->limit(4)
                    ->limitedRemainingText(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Album / kategorija')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('images_count')
                    ->counts('images')
                    ->label('Broj fotografija')
                    ->badge(),
                Tables\Columns\TextColumn::make('videos_count')
                    ->counts('videos')
                    ->label('Broj videa')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Posljednja izmjena')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Otvori album'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Još nemate portfolio albume')
            ->emptyStateDescription('Kreirajte album odabirom kategorije i dodajte fotografije ili video zapise.')
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            VideosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolioAlbums::route('/'),
            'create' => Pages\CreatePortfolioAlbum::route('/create'),
            'edit' => Pages\EditPortfolioAlbum::route('/{record}/edit'),
        ];
    }
}
