<?php

namespace App\Filament\Resources;

use App\Enums\ProfileType;
use App\Enums\ServiceType;
use App\Filament\Resources\PhotographerProfileResource\Pages;
use App\Models\PhotographerProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PhotographerProfileResource extends Resource
{
    protected static ?string $model = PhotographerProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationGroup = 'Fotografi';

    protected static ?string $navigationLabel = 'Profili fotografa';

    protected static ?string $modelLabel = 'profil fotografa';

    protected static ?string $pluralModelLabel = 'profili fotografa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Osnovni podaci')->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')->searchable()->required()->label('Korisnik'),
                Forms\Components\TextInput::make('display_name')->label('Javni naziv')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                Forms\Components\Select::make('profile_type')->label('Tip profila')->options(ProfileType::options())->required(),
                Forms\Components\Select::make('service_type')->label('Tip usluge')->options(ServiceType::options())->required(),
                Forms\Components\TextInput::make('first_name')->label('Ime'),
                Forms\Components\TextInput::make('last_name')->label('Prezime'),
                Forms\Components\TextInput::make('company_name')->label('Naziv firme'),
                Forms\Components\TextInput::make('company_tax_number')->label('PIB / OIB / VAT'),
                Forms\Components\TextInput::make('experience_years')->label('Godine iskustva')->numeric()->minValue(0)->maxValue(80),
            ])->columns(2),

            Forms\Components\Section::make('Slike i opis')->schema([
                Forms\Components\FileUpload::make('profile_image')->label('Profilna slika')->webp('profiles', 800),
                Forms\Components\FileUpload::make('cover_image')->label('Cover slika')->webp('covers', 1920),
                Forms\Components\Textarea::make('about')->label('Opis')->rows(5)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Kontakt')->schema([
                Forms\Components\TextInput::make('phone')->label('Telefon')->tel(),
                Forms\Components\TextInput::make('secondary_phone')->label('Drugi telefon')->tel(),
                Forms\Components\TextInput::make('public_email')->label('Javni e-mail')->email(),
                Forms\Components\TextInput::make('website')->label('Web stranica')->url(),
            ])->columns(2),

            Forms\Components\Section::make('Lokacije i kategorije')->schema([
                Forms\Components\Select::make('primary_country_id')->label('Primarna država')->relationship('primaryCountry', 'name')->searchable(),
                Forms\Components\Select::make('primary_city_id')->label('Primarni grad')->relationship('primaryCity', 'name')->searchable(),
                Forms\Components\Select::make('categories')->label('Kategorije')->relationship('categories', 'name')->multiple()->preload()->columnSpanFull(),
                Forms\Components\Select::make('cities')->label('Gradovi rada')->relationship('cities', 'name')->multiple()->preload()->columnSpanFull(),
                Forms\Components\Select::make('countries')->label('Države rada')->relationship('countries', 'name')->multiple()->preload()->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Status')->schema([
                Forms\Components\Toggle::make('active')->label('Aktivan (odobren)'),
                Forms\Components\Toggle::make('verified')->label('Verifikovan'),
                Forms\Components\Toggle::make('featured')->label('Istaknut'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')->label('')->circular()->disk('public'),
                Tables\Columns\TextColumn::make('display_name')->label('Naziv')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('service_type')->label('Usluga')
                    ->formatStateUsing(fn ($state) => $state instanceof ServiceType ? $state->label() : $state)->badge(),
                Tables\Columns\TextColumn::make('primaryCity.name')->label('Grad')->sortable(),
                Tables\Columns\ToggleColumn::make('active')->label('Aktivan'),
                Tables\Columns\ToggleColumn::make('verified')->label('Verifikovan'),
                Tables\Columns\ToggleColumn::make('featured')->label('Istaknut'),
                Tables\Columns\TextColumn::make('profile_views')->label('Pregledi')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Kreiran')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')->label('Aktivan'),
                Tables\Filters\TernaryFilter::make('verified')->label('Verifikovan'),
                Tables\Filters\TernaryFilter::make('featured')->label('Istaknut'),
                Tables\Filters\SelectFilter::make('service_type')->label('Tip usluge')->options(ServiceType::options()),
                Tables\Filters\SelectFilter::make('profile_type')->label('Tip profila')->options(ProfileType::options()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')->label('Odobri')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (PhotographerProfile $record) => ! $record->active)
                    ->action(fn (PhotographerProfile $record) => $record->update(['active' => true]))
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')->label('Aktiviraj')->icon('heroicon-o-check')->color('success')
                        ->action(fn ($records) => $records->each->update(['active' => true]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')->label('Deaktiviraj')->icon('heroicon-o-x-mark')->color('danger')
                        ->action(fn ($records) => $records->each->update(['active' => false]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotographerProfiles::route('/'),
            'create' => Pages\CreatePhotographerProfile::route('/create'),
            'edit' => Pages\EditPhotographerProfile::route('/{record}/edit'),
        ];
    }
}
