<?php

namespace App\Filament\Resources\PortfolioAlbumResource\RelationManagers;

use App\Models\PortfolioVideo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'Video portfolio';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Naslov')->maxLength(255),
            Forms\Components\TextInput::make('url')
                ->label('YouTube ili Vimeo link')
                ->url()
                ->required()
                ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                    if (PortfolioVideo::parseVideoUrl((string) $value) === null) {
                        $fail('Unesite ispravan YouTube ili Vimeo link.');
                    }
                })
                ->helperText('Zalijepite link sa YouTube-a ili Vimeo-a; sistem ce ga sigurno embedovati na profilu.'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->label('Redoslijed'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Naslov')->placeholder('Bez naslova')->limit(40),
                Tables\Columns\TextColumn::make('provider')->label('Platforma')->badge(),
                Tables\Columns\TextColumn::make('url')->label('Link')->limit(45),
                Tables\Columns\TextColumn::make('sort_order')->label('Redoslijed')->sortable(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Dodaj video'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
