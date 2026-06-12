<?php

namespace App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers;

use App\Models\PortfolioAlbum;
use App\Models\PortfolioVideo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'Video zapisi';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof PortfolioAlbum
            && $ownerRecord->photographer_profile_id === auth()->user()?->photographerProfile?->id;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('url')
                ->label('YouTube ili Vimeo link')
                ->url()
                ->required()
                ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                    if (PortfolioVideo::parseVideoUrl((string) $value) === null) {
                        $fail('Unesite ispravan YouTube ili Vimeo link.');
                    }
                })
                ->helperText('Zalijepite javni YouTube ili Vimeo link. Video će se prikazati u ovoj kategoriji.'),
            Forms\Components\TextInput::make('title')
                ->label('Naslov videa')
                ->helperText('Opcionalno. Ako ostavite prazno, prikazat će se samo video.')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Naslov')
                    ->placeholder('Bez naslova')
                    ->limit(40),
                Tables\Columns\TextColumn::make('provider')
                    ->label('Platforma')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'youtube' => 'YouTube',
                        'vimeo' => 'Vimeo',
                        default => ucfirst($state),
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Link')
                    ->limit(45)
                    ->url(fn (PortfolioVideo $record): string => $record->url)
                    ->openUrlInNewTab(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj video')
                    ->icon('heroicon-o-video-camera')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = ((int) $this->getOwnerRecord()->videos()->max('sort_order')) + 1;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Još nema video zapisa')
            ->emptyStateDescription('Dodajte YouTube ili Vimeo video u ovu kategoriju.');
    }
}
