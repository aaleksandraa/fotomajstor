<?php

namespace App\Filament\Resources;

use App\Enums\PhotographerBlogStatus;
use App\Filament\Resources\PhotographerBlogPostResource\Pages;
use App\Models\PhotographerBlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PhotographerBlogPostResource extends Resource
{
    protected static ?string $model = PhotographerBlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Fotografi';

    protected static ?string $navigationLabel = 'Blog fotografa (moderacija)';

    protected static ?string $modelLabel = 'članak fotografa';

    protected static ?string $pluralModelLabel = 'članci fotografa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('photographer_profile_id')->label('Fotograf')
                ->relationship('photographerProfile', 'display_name')->searchable()->required(),
            Forms\Components\TextInput::make('title')->label('Naslov')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->required()->maxLength(255),
            Forms\Components\Textarea::make('excerpt')->label('Sažetak')->rows(2)->columnSpanFull(),
            Forms\Components\RichEditor::make('content')->label('Sadržaj')->columnSpanFull(),
            Forms\Components\FileUpload::make('featured_image')->label('Glavna slika')->webp('blog', 1600),
            Forms\Components\Select::make('category_id')->label('Kategorija')->relationship('category', 'name')->searchable(),
            Forms\Components\Select::make('city_id')->label('Grad')->relationship('city', 'name')->searchable(),
            Forms\Components\Select::make('status')->label('Status')->options(PhotographerBlogStatus::options())->required(),
            Forms\Components\Textarea::make('rejection_reason')->label('Razlog odbijanja')->rows(2)
                ->visible(fn (Forms\Get $get) => $get('status') === PhotographerBlogStatus::Rejected->value),
            Forms\Components\DateTimePicker::make('published_at')->label('Objavljeno'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Naslov')->searchable()->limit(40)->weight('bold'),
                Tables\Columns\TextColumn::make('photographerProfile.display_name')->label('Fotograf')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PhotographerBlogStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof PhotographerBlogStatus ? $state->color() : 'gray'),
                Tables\Columns\TextColumn::make('published_at')->label('Objavljeno')->dateTime('d.m.Y.')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(PhotographerBlogStatus::options()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')->label('Odobri')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (PhotographerBlogPost $record) => $record->status !== PhotographerBlogStatus::Published)
                    ->action(fn (PhotographerBlogPost $record) => $record->update([
                        'status' => PhotographerBlogStatus::Published,
                        'published_at' => $record->published_at ?? now(),
                        'rejection_reason' => null,
                    ]))->requiresConfirmation(),
                Tables\Actions\Action::make('reject')->label('Odbij')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (PhotographerBlogPost $record) => $record->status !== PhotographerBlogStatus::Rejected)
                    ->form([Forms\Components\Textarea::make('rejection_reason')->label('Razlog odbijanja')->required()])
                    ->action(fn (PhotographerBlogPost $record, array $data) => $record->update([
                        'status' => PhotographerBlogStatus::Rejected,
                        'rejection_reason' => $data['rejection_reason'],
                    ])),
                Tables\Actions\EditAction::make(),
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
