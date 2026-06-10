@extends('layouts.app')

@section('content')
    <section class="container-px pt-10">
        <p class="eyebrow">{{ __('Lokacije') }}</p>
        <h1 class="mt-2 font-serif text-4xl text-ink-900 sm:text-5xl">{{ __('Gradovi i mjesta') }}</h1>
        <p class="mt-3 max-w-2xl text-ink-600">{{ __('Pronađite fotografe i videografe po gradovima u Bosni i Hercegovini, Srbiji, Hrvatskoj, Sloveniji i Crnoj Gori.') }}</p>
    </section>

    <section class="container-px mt-8 space-y-10 pb-16">
        @foreach ($countries as $country)
            <div>
                <h2 class="font-serif text-2xl text-ink-900">{{ $country->name }}</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($country->cities as $city)
                        <a href="{{ localized_route('landing.country.city', [$country->slug, $city->slug]) }}"
                           class="flex items-center justify-between rounded-xl border border-ink-100 bg-white px-4 py-3 transition hover:border-ink-300 hover:shadow-sm">
                            <span class="flex items-center gap-2 text-sm font-medium text-ink-800">
                                <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-5.2-7-11a7 7 0 1 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                {{ $city->name }}
                            </span>
                            <span class="text-xs text-ink-400">{{ $city->photographers_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endsection
