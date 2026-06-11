<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class DashboardRegistrationResponse implements RegistrationResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()
            ->to(Filament::getEmailVerificationPromptUrl() ?? Filament::getUrl())
            ->with('registration_success', true);
    }
}
