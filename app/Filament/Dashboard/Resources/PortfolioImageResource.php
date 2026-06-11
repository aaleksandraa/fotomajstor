<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\PortfolioImageResource\Pages;
use App\Models\Category;
use App\Models\PortfolioImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PortfolioImageResource extends Resource
{
    protected static ?string $model = PortfolioImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Portfolio';

    protected static ?string $modelLabel = 'fotografija';

    protected static ?string $pluralModelLabel = 'fotografije';

    protected static ?int $navigationSort = 2;

    protected static function profileId(): ?int
    {
        return auth()->user()?->photographerProfile?->id;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('album', fn (Builder $query) => $query->where('photographer_profile_id', static::profileId()));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->label('Kategorija')
                ->options(fn () => Category::active()->ordered()->pluck('name', 'id'))
                ->helperText('Odaberite postojeću kategoriju kojoj fotografija pripada.')
                ->searchable()
                ->required()
                ->native(false),
            Forms\Components\FileUpload::make('image_path')
                ->label('Fotografija')
                ->webp('portfolio', 1600)
                ->imageEditor()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->label('Fotografija')->disk('public')->square(),
                Tables\Columns\TextColumn::make('album.category.name')->label('Kategorija')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Dodano')->dateTime('d.m.Y. H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolioImages::route('/'),
            'create' => Pages\CreatePortfolioImage::route('/create'),
            'edit' => Pages\EditPortfolioImage::route('/{record}/edit'),
        ];
    }
}
