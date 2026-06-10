<?php

namespace App\Filament\Dashboard\Pages\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $data = $this->form->getState();

        $user = User::query()->where('email', $data['email'])->first();

        if (
            $user?->role === UserRole::Admin
            && auth()->getProvider()->validateCredentials($user, ['password' => $data['password']])
        ) {
            throw ValidationException::withMessages([
                'data.email' => __('Ovo je administratorski nalog. Prijavite se na :url', ['url' => url('/admin/login')]),
            ]);
        }

        return parent::authenticate();
    }
}
