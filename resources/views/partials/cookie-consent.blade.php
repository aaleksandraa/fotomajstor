@php
    $googleAnalyticsId = trim((string) config('services.google.analytics_id'));
    $googleAnalyticsEnabled = preg_match('/^G-[A-Z0-9]+$/i', $googleAnalyticsId) === 1;
@endphp

@if ($googleAnalyticsEnabled)
<div
    x-data="{
        open: false,
        key: {{ Illuminate\Support\Js::from(config('legal.consent_storage_key')) }},
        init() {
            try { this.open = ! localStorage.getItem(this.key); } catch (error) { this.open = true; }
            window.addEventListener('fotomajstor:cookie-settings', () => this.open = true);
        },
        save(value) {
            try { localStorage.setItem(this.key, value); } catch (error) {}
            this.open = false;
            if (value === 'analytics' && window.fotoMajstorLoadAnalytics) {
                window.fotoMajstorLoadAnalytics();
            } else if (value === 'necessary' && window.gtag) {
                window.gtag('consent', 'update', { analytics_storage: 'denied' });
            }
        },
    }"
    x-show="open"
    x-transition.opacity
    x-cloak
    class="fixed inset-x-3 bottom-3 z-[100] mx-auto max-w-3xl rounded-2xl border border-ink-200 bg-white p-5 shadow-2xl sm:bottom-5 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cookie-consent-title"
>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
        <div class="max-w-xl">
            <h2 id="cookie-consent-title" class="font-serif text-xl text-ink-900">{{ __('Vaša privatnost') }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink-600">
                {{ __('Koristimo nužne kolačiće za rad i sigurnost stranice. Google Analytics aktiviramo samo uz vašu saglasnost kako bismo razumjeli korištenje i unaprijedili FotoMajstor.') }}
            </p>
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs font-medium">
                <a href="{{ localized_route('privacy') }}" class="text-accent-700 hover:text-accent-900">{{ __('Politika privatnosti') }}</a>
                <a href="{{ localized_route('terms') }}" class="text-accent-700 hover:text-accent-900">{{ __('Uslovi korištenja') }}</a>
            </div>
        </div>
        <div class="flex shrink-0 flex-col gap-2 sm:min-w-44">
            <button type="button" @click="save('analytics')" class="btn-primary">{{ __('Prihvati analitiku') }}</button>
            <button type="button" @click="save('necessary')" class="btn-outline">{{ __('Samo nužni') }}</button>
        </div>
    </div>
</div>
@endif
