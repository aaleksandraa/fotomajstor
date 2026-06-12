<?php

namespace App\Filament\Dashboard\Pages;

use App\Enums\ProfileType;
use App\Enums\ServiceType;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\PhotographerProfile;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Moj profil';

    protected static ?string $title = 'Uređivanje profila';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.dashboard.pages.edit-profile';

    /** @var array<string, mixed> */
    public array $data = [];

    public ?PhotographerProfile $profile = null;

    public function mount(): void
    {
        $this->profile = auth()->user()->photographerProfile()->with('socialLinks', 'categories', 'cities')->firstOrFail();

        $state = $this->profile->attributesToArray();
        $state['social'] = $this->profile->socialLinks?->only(['instagram', 'facebook', 'tiktok', 'youtube', 'linkedin']) ?? [];
        $state['categories'] = $this->profile->categories->pluck('id')->all();
        $state['cities'] = $this->profile->cities->pluck('id')->all();

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Section::make('Osnovni podaci')->schema([
                Forms\Components\Select::make('profile_type')->label(__('Tip profila'))->options(ProfileType::options())->required(),
                Forms\Components\Select::make('service_type')
                    ->label(__('Tip usluge'))
                    ->helperText(__('Odaberite Fotograf, Videograf ili Fotograf & Videograf.'))
                    ->options(ServiceType::options())
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('display_name')->label(__('Javni naziv'))->required()->maxLength(255),
                Forms\Components\TextInput::make('first_name')->label(__('Ime')),
                Forms\Components\TextInput::make('last_name')->label(__('Prezime')),
                Forms\Components\TextInput::make('company_name')->label(__('Naziv firme')),
                Forms\Components\TextInput::make('company_tax_number')->label(__('PIB / OIB / VAT (opciono)')),
                Forms\Components\TextInput::make('experience_years')->label(__('Godine iskustva'))->numeric()->minValue(0)->maxValue(80),
            ])->columns(2),

            Section::make('Slike i opis')->schema([
                Forms\Components\FileUpload::make('profile_image')->label(__('Profilna slika'))->webp('profiles', 800)->imageEditor(),
                Forms\Components\FileUpload::make('cover_image')->label(__('Cover slika'))->webp('covers', 1920)->imageEditor(),
                Forms\Components\Textarea::make('about')->label(__('O meni'))->rows(5)->columnSpanFull()
                    ->helperText(__('Preporučeno najmanje 100 karaktera.')),
            ])->columns(2),

            Section::make('Kontakt')->schema([
                Forms\Components\TextInput::make('phone')->label(__('Telefon'))->tel(),
                Forms\Components\TextInput::make('secondary_phone')->label(__('Drugi telefon'))->tel(),
                Forms\Components\TextInput::make('public_email')->label(__('Javni e-mail'))->email(),
                Forms\Components\TextInput::make('website')->label(__('Web stranica'))->url(),
            ])->columns(2),

            Section::make('Društvene mreže')->schema([
                Forms\Components\TextInput::make('social.instagram')->label('Instagram')->url(),
                Forms\Components\TextInput::make('social.facebook')->label('Facebook')->url(),
                Forms\Components\TextInput::make('social.tiktok')->label('TikTok')->url(),
                Forms\Components\TextInput::make('social.youtube')->label('YouTube')->url(),
                Forms\Components\TextInput::make('social.linkedin')->label('LinkedIn')->url(),
            ])->columns(2),

            Section::make('Lokacije i kategorije')->schema([
                Forms\Components\Select::make('primary_country_id')->label(__('Primarna država'))
                    ->options(fn () => Country::active()->ordered()->pluck('name', 'id'))->searchable(),
                Forms\Components\Select::make('primary_city_id')->label(__('Primarni grad'))
                    ->options(fn () => City::active()->ordered()->pluck('name', 'id'))->searchable(),
                Forms\Components\Select::make('categories')->label(__('Kategorije koje radim'))
                    ->options(fn () => Category::active()->ordered()->pluck('name', 'id'))->multiple()->searchable()->columnSpanFull(),
                Forms\Components\Select::make('cities')->label(__('Gradovi u kojima radim'))
                    ->options(fn () => City::active()->ordered()->pluck('name', 'id'))->multiple()->searchable()->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $hasContact = filled($data['phone'] ?? null) || filled($data['public_email'] ?? null) || filled($data['social']['instagram'] ?? null);
        if (! $hasContact) {
            throw ValidationException::withMessages([
                'data.phone' => 'Unesite barem jedan kontakt: telefon, javni e-mail ili Instagram.',
            ]);
        }

        $categories = $data['categories'] ?? [];
        $cities = $data['cities'] ?? [];
        $social = $data['social'] ?? [];
        unset($data['categories'], $data['cities'], $data['social'], $data['id'], $data['user_id'], $data['slug'],
            $data['active'], $data['verified'], $data['featured'], $data['profile_views'], $data['created_at'], $data['updated_at']);

        $data['onboarding_completed_at'] = $this->profile->onboarding_completed_at ?? now();
        $this->profile->update($data);
        $this->profile->socialLinks()->updateOrCreate([], $social);
        $this->profile->categories()->sync($categories);
        $this->profile->cities()->sync($cities);
        $this->profile->countries()->sync(
            City::whereIn('id', $cities)->pluck('country_id')->unique()->all()
        );
        auth()->user()->publishVerifiedPhotographerProfile();

        Notification::make()->title('Profil je sačuvan')->success()->send();
    }
}
