<?php

namespace App\Http\Responses;

use App\Filament\Dashboard\Pages\EditProfile;
use Filament\Http\Responses\Auth\Contracts\EmailVerificationResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class DashboardEmailVerificationResponse implements EmailVerificationResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        auth()->user()?->publishVerifiedPhotographerProfile();

        return redirect()
            ->to(EditProfile::getUrl(panel: 'dashboard'))
            ->with('email_verified', true);
    }
}
