@php
    $footerCategories = \App\Models\Category::active()->ordered()->take(6)->get();
    $footerCities = \App\Models\City::active()->ordered()->with('country')->take(6)->get();
    $accountUrl = auth()->check()
        ? account_dashboard_url()
        : route('locale.switch', ['locale' => app()->getLocale(), 'redirect' => url('/dashboard/register')]);
    $accountLabel = auth()->check() ? __('Dashboard') : __('Postani fotograf');
@endphp
<footer class="mt-24 border-t border-ink-100 bg-ink-50">
    <div class="container-px grid grid-cols-2 gap-8 py-14 md:grid-cols-4">
        <div class="col-span-2 md:col-span-1">
            <a href="{{ localized_route('home') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-ink-900 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 7 8 5h8l1.5 2H20a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h2.5Z"/><circle cx="12" cy="12.5" r="3.2"/></svg>
                </span>
                <span class="font-serif text-lg text-ink-900">Foto<span class="text-ink-400">Majstor</span></span>
            </a>
            <p class="mt-4 max-w-xs text-sm text-ink-500">{{ __('Regionalni katalog fotografa i videografa za BiH, Srbiju, Hrvatsku, Sloveniju i Crnu Goru.') }}</p>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-ink-900">{{ __('Kategorije') }}</h4>
            <ul class="mt-4 space-y-2">
                @foreach ($footerCategories as $category)
                    <li><a href="{{ localized_route('category.show', $category->slug) }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-ink-900">{{ __('Gradovi') }}</h4>
            <ul class="mt-4 space-y-2">
                @foreach ($footerCities as $city)
                    <li><a href="{{ localized_route('landing.country.city', [$city->country->slug, $city->slug]) }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ $city->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-ink-900">{{ __('Platforma') }}</h4>
            <ul class="mt-4 space-y-2">
                <li><a href="{{ localized_route('search') }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ __('Pretraga') }}</a></li>
                <li><a href="{{ localized_route('blog.index') }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ __('Blog') }}</a></li>
                @guest
                    <li><a href="{{ route('locale.switch', ['locale' => app()->getLocale(), 'redirect' => url('/dashboard')]) }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ __('Prijava za fotografe') }}</a></li>
                @endguest
                <li><a href="{{ $accountUrl }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ $accountLabel }}</a></li>
                <li><a href="{{ localized_route('privacy') }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ __('Politika privatnosti') }}</a></li>
                <li><a href="{{ localized_route('terms') }}" class="text-sm text-ink-500 transition hover:text-ink-900">{{ __('Uslovi korištenja') }}</a></li>
                <li><button type="button" onclick="window.dispatchEvent(new Event('fotomajstor:cookie-settings'))" class="text-left text-sm text-ink-500 transition hover:text-ink-900">{{ __('Postavke kolačića') }}</button></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-ink-100">
        <div class="container-px flex flex-col items-center justify-between gap-2 py-6 text-sm text-ink-400 md:flex-row">
            <p>&copy; {{ date('Y') }} FotoMajstor. {{ __('Sva prava zadržana.') }}</p>
            <p>{{ __('Kontakt direktno sa fotografima — bez provizije.') }}</p>
        </div>
    </div>
</footer>
