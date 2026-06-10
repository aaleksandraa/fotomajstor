<?php

namespace App\Filament\Dashboard\Pages\Auth;

use App\Enums\ProfileType;
use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Models\City;
use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getProfileTypeFormComponent(),
                        $this->getDisplayNameFormComponent(),
                        $this->getServiceTypeFormComponent(),
                        $this->getCityFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getDisplayNameFormComponent(): Component
    {
        return TextInput::make('display_name')
            ->label(__('Naziv firme'))
            ->helperText(__('Tako će vaš profil biti prikazan posjetiocima.'))
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('profile_type') === ProfileType::Company->value)
            ->required(fn (Get $get): bool => $get('profile_type') === ProfileType::Company->value);
    }

    protected function getProfileTypeFormComponent(): Component
    {
        return Select::make('profile_type')
            ->label(__('Tip profila'))
            ->options(ProfileType::options())
            ->default(ProfileType::Individual->value)
            ->required()
            ->live()
            ->native(false);
    }

    protected function getServiceTypeFormComponent(): Component
    {
        return Select::make('service_type')
            ->label(__('Vrsta usluge'))
            ->options(ServiceType::options())
            ->default(ServiceType::Photographer->value)
            ->required()
            ->native(false);
    }

    protected function getCityFormComponent(): Component
    {
        return Select::make('primary_city_id')
            ->label(__('Primarni grad'))
            ->options(fn () => City::query()
                ->with('country')
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (City $city) => [$city->id => $city->name.' — '.$city->country?->name]))
            ->searchable()
            ->required()
            ->native(false);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::Photographer,
        ]);

        // UserObserver kreira osnovni (neaktivan) profil; ovdje ga obogaćujemo podacima iz forme.
        $profile = $user->photographerProfile()->first();

        if ($profile) {
            $city = ! empty($data['primary_city_id']) ? City::find($data['primary_city_id']) : null;

            $isCompany = ($data['profile_type'] ?? null) === ProfileType::Company->value;
            $displayName = ($isCompany && filled($data['display_name'] ?? null))
                ? $data['display_name']
                : $user->name;

            $profile->update(array_filter([
                'display_name' => $displayName,
                'profile_type' => $data['profile_type'] ?? null,
                'service_type' => $data['service_type'] ?? null,
                'primary_city_id' => $city?->id,
                'primary_country_id' => $city?->country_id,
            ], fn ($value) => filled($value)));

            if ($city) {
                $profile->cities()->syncWithoutDetaching([$city->id]);
                $profile->countries()->syncWithoutDetaching([$city->country_id]);
            }
        }

        return $user;
    }
}
