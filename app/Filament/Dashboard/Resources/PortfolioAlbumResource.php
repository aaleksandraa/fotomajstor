<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers\ImagesRelationManager;
use App\Models\Category;
use App\Models\PortfolioAlbum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PortfolioAlbumResource extends Resource
{
    protected static ?string $model = PortfolioAlbum::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Portfolio';

    protected static ?string $modelLabel = 'album';

    protected static ?string $pluralModelLabel = 'albumi';

    protected static function profileId(): ?int
    {
        return auth()->user()?->photographerProfile?->id;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('photographer_profile_id', static::profileId());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('photographer_profile_id')->default(fn () => static::profileId()),
            Forms\Components\TextInput::make('title')->label('Naslov albuma')->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required(),
            Forms\Components\Select::make('category_id')->label('Kategorija')
                ->options(fn () => Category::active()->ordered()->pluck('name', 'id'))->searchable(),
            Forms\Components\Textarea::make('description')->label('Opis')->columnSpanFull(),
            Forms\Components\FileUpload::make('cover_image')->label('Cover slika')->webp('albums'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->label('Redoslijed'),
            Forms\Components\Toggle::make('active')->label('Aktivan')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->label('')->disk('public'),
                Tables\Columns\TextColumn::make('title')->label('Naslov')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')->label('Kategorija'),
                Tables\Columns\TextColumn::make('images_count')->counts('images')->label('Slika'),
                Tables\Columns\TextColumn::make('videos_count')->counts('videos')->label('Video'),
                Tables\Columns\IconColumn::make('active')->label('Aktivan')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [ImagesRelationManager::class, \App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers\VideosRelationManager::class];
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
