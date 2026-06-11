@if (session('registration_success'))
    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-950 dark:text-emerald-200">
        <p class="font-semibold">{{ __('Registracija je uspješna.') }}</p>
        <p class="mt-1">{{ __('Provjerite svoj e-mail i kliknite na link za potvrdu profila.') }}</p>
    </div>
@endif
