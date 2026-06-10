<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioAlbumResource\Pages;
use App\Models\PortfolioAlbum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioAlbumResource extends Resource
{
    protected static ?string $model = PortfolioAlbum::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Fotografi';

    protected static ?string $navigationLabel = 'Portfolio albumi';

    protected static ?string $modelLabel = 'album';

    protected static ?string $pluralModelLabel = 'albumi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('photographer_profile_id')->label('Fotograf')
                ->relationship('photographerProfile', 'display_name')->searchable()->required(),
            Forms\Components\Select::make('category_id')->label('Kategorija')->relationship('category', 'name')->searchable(),
            Forms\Components\TextInput::make('title')->label('Naslov')->required(),
            Forms\Components\TextInput::make('slug')->required(),
            Forms\Components\Textarea::make('description')->label('Opis')->columnSpanFull(),
            Forms\Components\FileUpload::make('cover_image')->label('Cover slika')->webp('albums'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('active')->label('Aktivan')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->label('')->disk('public'),
                Tables\Columns\TextColumn::make('title')->label('Naslov')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('photographerProfile.display_name')->label('Fotograf')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategorija'),
                Tables\Columns\TextColumn::make('images_count')->counts('images')->label('Slika'),
                Tables\Columns\TextColumn::make('videos_count')->counts('videos')->label('Video'),
                Tables\Columns\IconColumn::make('active')->label('Aktivan')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')->label('Aktivan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PortfolioAlbumResource\RelationManagers\ImagesRelationManager::class,
            PortfolioAlbumResource\RelationManagers\VideosRelationManager::class,
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
