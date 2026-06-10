@extends('layouts.app')

@php
    $today = now()->toDateString();
    $onlyAvailable = ($filters['date'] ?? null) === $today;
@endphp

@section('content')
    <section class="container-px pt-10">
        <p class="eyebrow">{{ __('Pretraga') }}</p>
        <h1 class="mt-2 font-serif text-4xl text-ink-900 sm:text-5xl">{{ __('Fotografi i videografi') }}</h1>
        <p class="mt-2 text-ink-500">{{ __('Pronađeno') }} <span class="font-semibold text-ink-800">{{ $total }}</span> {{ __('profesionalaca') }}</p>

        <form action="{{ localized_route('search') }}" method="GET" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            @foreach (['category', 'city', 'country', 'date', 'service_type', 'profile_type'] as $hidden)
                @if (! empty($filters[$hidden]))
                    <input type="hidden" name="{{ $hidden }}" value="{{ $filters[$hidden] }}">
                @endif
            @endforeach
            <div class="relative flex-1">
                <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3-3"/></svg>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Pretraži po imenu, gradu...') }}"
                       class="w-full rounded-full border-ink-200 bg-white py-3 pl-12 pr-4 text-sm focus:border-ink-400 focus:ring-0">
            </div>
            <select name="sort" onchange="this.form.submit()" class="rounded-full border-ink-200 bg-white py-3 pl-4 pr-10 text-sm focus:border-ink-400 focus:ring-0">
                <option value="relevant" @selected(($filters['sort'] ?? '') === 'relevant')>{{ __('Sortiraj: Relevantno') }}</option>
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>{{ __('Najnovije') }}</option>
                <option value="experience" @selected(($filters['sort'] ?? '') === 'experience')>{{ __('Iskustvo') }}</option>
                <option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>{{ __('Najpopularnije') }}</option>
            </select>
        </form>
    </section>

    <section class="container-px mt-8 grid grid-cols-1 gap-8 pb-16 lg:grid-cols-[260px_1fr]">
        {{-- Sidebar filters --}}
        <aside class="h-fit rounded-2xl border border-ink-100 bg-white p-5 lg:sticky lg:top-20">
            <a href="{{ request()->fullUrlWithQuery(['date' => $onlyAvailable ? null : $today, 'page' => null]) }}"
               class="flex items-center justify-between rounded-xl border {{ $onlyAvailable ? 'border-emerald-200 bg-emerald-50' : 'border-ink-100' }} px-3 py-2.5">
                <span class="inline-flex items-center gap-2 text-sm font-medium text-ink-800">
                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                    {{ __('Samo dostupni') }}
                </span>
                <span class="relative h-5 w-9 rounded-full transition {{ $onlyAvailable ? 'bg-emerald-500' : 'bg-ink-200' }}">
                    <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white transition {{ $onlyAvailable ? 'left-4' : 'left-0.5' }}"></span>
                </span>
            </a>

            <div class="mt-6">
                <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-accent-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 7h18M6 12h12M9 17h6"/></svg> {{ __('Kategorija') }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="pill {{ empty($filters['category']) ? 'pill-active' : '' }}">{{ __('Sve') }}</a>
                    @foreach ($categories->take(11) as $category)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $category->slug, 'page' => null]) }}"
                           class="pill {{ ($filters['category'] ?? '') === $category->slug ? 'pill-active' : '' }}">{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">
                <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-accent-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-5.2-7-11a7 7 0 1 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg> {{ __('Grad') }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['city' => null, 'page' => null]) }}" class="pill {{ empty($filters['city']) ? 'pill-active' : '' }}">{{ __('Svi') }}</a>
                    @foreach ($cities->take(10) as $city)
                        <a href="{{ request()->fullUrlWithQuery(['city' => $city->slug, 'page' => null]) }}"
                           class="pill {{ ($filters['city'] ?? '') === $city->slug ? 'pill-active' : '' }}">{{ $city->name }}</a>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">
                <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-accent-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="m12 3 2.6 5.3 5.8.8-4.2 4.1 1 5.8L12 16.9 6.8 19l1-5.8L3.6 9.1l5.8-.8z"/></svg> {{ __('Tip usluge') }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['service_type' => null, 'page' => null]) }}" class="pill {{ empty($filters['service_type']) ? 'pill-active' : '' }}">{{ __('Sve') }}</a>
                    @foreach ($serviceTypes as $value => $label)
                        <a href="{{ request()->fullUrlWithQuery(['service_type' => $value, 'page' => null]) }}"
                           class="pill {{ ($filters['service_type'] ?? '') === $value ? 'pill-active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- Results --}}
        <div>
            <p class="mb-4 text-sm text-ink-500">{{ __('Prikazano :shown od :total rezultata', ['shown' => $photographers->count(), 'total' => $total]) }}</p>
            @if ($photographers->isEmpty())
                <div class="rounded-2xl border border-dashed border-ink-200 p-12 text-center">
                    <p class="font-serif text-xl text-ink-900">{{ __('Nema rezultata') }}</p>
                    <p class="mt-2 text-sm text-ink-500">{{ __('Pokušajte ublažiti filtere ili promijeniti datum.') }}</p>
                    <a href="{{ localized_route('search') }}" class="btn-outline mt-4">{{ __('Poništi filtere') }}</a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($photographers as $photographer)
                        <x-photographer-card :photographer="$photographer" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $photographers->links() }}</div>
            @endif
        </div>
    </section>
@endsection
