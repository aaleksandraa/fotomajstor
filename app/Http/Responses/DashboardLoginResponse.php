<?php

namespace App\Http\Responses;

use App\Filament\Dashboard\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class DashboardLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $profile = auth()->user()?->photographerProfile()->first();

        if ($profile && blank($profile->onboarding_completed_at)) {
            return redirect()->to(EditProfile::getUrl(panel: 'dashboard'));
        }

        return redirect()->intended(Filament::getUrl());
    }
}
