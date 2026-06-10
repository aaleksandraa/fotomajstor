<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Šifarnik';

    protected static ?string $navigationLabel = 'Hijerarhija lokacija';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('country_id')->relationship('country', 'name')->required(),
            Forms\Components\Select::make('parent_id')->relationship('parent', 'name')->searchable()->preload(),
            Forms\Components\Select::make('type')->options([
                'region' => 'Regija',
                'city' => 'Grad',
                'municipality' => 'Opština / općina',
                'place' => 'Mjesto',
            ])->required(),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('slug')->required(),
            Forms\Components\TextInput::make('region'),
            Forms\Components\TextInput::make('meta_title')->columnSpanFull(),
            Forms\Components\Textarea::make('meta_description')->columnSpanFull(),
            Forms\Components\Textarea::make('intro_text')->columnSpanFull(),
            Forms\Components\Toggle::make('active')->required(),
            Forms\Components\Toggle::make('indexable')->helperText('Lokacija bez aktivnog profesionalca treba ostati isključena.'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')->sortable(),
                Tables\Columns\TextColumn::make('parent.name')->label('Roditelj'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('active')->boolean(),
                Tables\Columns\IconColumn::make('indexable')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
