<?php

namespace App\Filament\Resources\PortfolioAlbumResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Slike';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image_path')->label('Slika')->webp('portfolio', 1600)->required(),
            Forms\Components\TextInput::make('title')->label('Naslov'),
            Forms\Components\TextInput::make('alt_text')->label('Alt tekst (SEO)')
                ->helperText('Ako ostavite prazno, sistem generiše alt tekst automatski.'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->label('Redoslijed'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->label('')->disk('public'),
                Tables\Columns\TextColumn::make('alt_text')->label('Alt tekst')->limit(40),
                Tables\Columns\TextColumn::make('sort_order')->label('Redoslijed')->sortable(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Dodaj sliku'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
