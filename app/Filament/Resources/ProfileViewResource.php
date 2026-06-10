<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileViewResource\Pages;
use App\Models\ProfileView;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProfileViewResource extends Resource
{
    protected static ?string $model = ProfileView::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Pregledi profila';

    protected static ?string $modelLabel = 'pregled profila';

    protected static ?string $pluralModelLabel = 'pregledi profila';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('photographerProfile.display_name')->label('Fotograf')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('viewed_at')->label('Vrijeme')->dateTime('d.m.Y. H:i')->sortable(),
                Tables\Columns\TextColumn::make('user_agent')->label('Uređaj')->limit(40)->toggleable(),
            ])
            ->defaultSort('viewed_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfileViews::route('/'),
        ];
    }
}
