@php
    $nav = [
        ['label' => __('Pretraga'), 'url' => localized_route('search')],
        ['label' => __('Kategorije'), 'url' => localized_route('categories.index')],
        ['label' => __('Gradovi'), 'url' => localized_route('cities.index')],
        ['label' => __('Blog'), 'url' => localized_route('blog.index')],
    ];
    $locales = config('locales.supported', []);
    $current = app()->getLocale();
    $accountUrl = auth()->check()
        ? account_dashboard_url()
        : route('locale.switch', ['locale' => $current, 'redirect' => url('/dashboard/register')]);
    $accountLabel = auth()->check() ? __('Dashboard') : __('Postani fotograf');
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-ink-100 bg-white/90 backdrop-blur">
    <div class="container-px flex h-16 items-center justify-between gap-4">
        <a href="{{ localized_route('home') }}" class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-ink-900 text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 7 8 5h8l1.5 2H20a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h2.5Z"/>
                    <circle cx="12" cy="12.5" r="3.2"/>
                </svg>
            </span>
            <span class="font-serif text-xl text-ink-900">Foto<span class="text-ink-400">.</span>Mreža</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex">
            @foreach ($nav as $item)
                <a href="{{ $item['url'] }}" class="text-sm font-medium text-ink-600 transition hover:text-ink-900">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            <x-language-switcher :locales="$locales" :current="$current" />
            <a href="{{ localized_route('search') }}" class="btn-outline">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3-3"/></svg>
                {{ __('Pretraži') }}
            </a>
            <a href="{{ $accountUrl }}" class="btn-primary">{{ $accountLabel }}</a>
        </div>

        <button @click="open = !open" class="md:hidden" aria-label="{{ __('Meni') }}">
            <svg class="h-6 w-6 text-ink-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>

    <div x-show="open" x-collapse class="border-t border-ink-100 md:hidden" style="display:none">
        <div class="container-px space-y-1 py-4">
            @foreach ($nav as $item)
                <a href="{{ $item['url'] }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-ink-700 hover:bg-ink-50">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ $accountUrl }}" class="btn-primary mt-2 w-full">{{ $accountLabel }}</a>
            <div class="mt-3 border-t border-ink-100 pt-3">
                <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-ink-400">{{ __('Jezik') }}</p>
                <div class="flex flex-wrap gap-1 px-3">
                    @foreach ($locales as $code => $meta)
                        <a href="{{ \App\Support\LocalizedUrl::for(request()->fullUrl(), $code) }}"
                           hreflang="{{ $meta['hreflang'] ?? $code }}"
                           class="rounded-md px-2.5 py-1 text-sm font-medium {{ $code === $current ? 'bg-ink-900 text-white' : 'text-ink-600 hover:bg-ink-50' }}">
                            {{ $meta['short'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</header>
