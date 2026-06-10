<?php

namespace App\Filament\Dashboard\Resources;

use App\Enums\PhotographerBlogStatus;
use App\Filament\Dashboard\Resources\PhotographerBlogPostResource\Pages;
use App\Models\Category;
use App\Models\City;
use App\Models\PhotographerBlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PhotographerBlogPostResource extends Resource
{
    protected static ?string $model = PhotographerBlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationLabel = 'Moj blog';

    protected static ?string $modelLabel = 'članak';

    protected static ?string $pluralModelLabel = 'članci';

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
            Forms\Components\Placeholder::make('rejection_notice')
                ->label('Razlog odbijanja')
                ->content(fn (?PhotographerBlogPost $record) => $record?->rejection_reason)
                ->visible(fn (?PhotographerBlogPost $record) => $record?->status === PhotographerBlogStatus::Rejected),
            Forms\Components\TextInput::make('title')->label('Naslov')->required()
                ->live(onBlur: true)->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required(),
            Forms\Components\Textarea::make('excerpt')->label('Kratak opis')->rows(2)->columnSpanFull(),
            Forms\Components\RichEditor::make('content')->label('Sadržaj')->required()->columnSpanFull(),
            Forms\Components\FileUpload::make('featured_image')->label('Glavna slika')->webp('blog', 1600),
            Forms\Components\Select::make('category_id')->label('Kategorija')
                ->options(fn () => Category::active()->ordered()->pluck('name', 'id'))->searchable(),
            Forms\Components\Select::make('city_id')->label('Grad')
                ->options(fn () => City::active()->ordered()->pluck('name', 'id'))->searchable(),
            Forms\Components\Select::make('status')->label('Status')
                ->options([
                    PhotographerBlogStatus::Draft->value => 'Sačuvaj kao nacrt',
                    PhotographerBlogStatus::Pending->value => 'Pošalji na odobrenje',
                ])
                ->default(PhotographerBlogStatus::Draft->value)
                ->helperText('Članak postaje javan tek nakon što ga administrator odobri.')
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Naslov')->searchable()->limit(40)->weight('bold'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PhotographerBlogStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof PhotographerBlogStatus ? $state->color() : 'gray'),
                Tables\Columns\TextColumn::make('published_at')->label('Objavljeno')->dateTime('d.m.Y.')->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotographerBlogPosts::route('/'),
            'create' => Pages\CreatePhotographerBlogPost::route('/create'),
            'edit' => Pages\EditPhotographerBlogPost::route('/{record}/edit'),
        ];
    }
}
