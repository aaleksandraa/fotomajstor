@extends('layouts.app')

@section('content')
    <section class="container-px pt-10">
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1 class="mt-2 max-w-3xl font-serif text-4xl text-ink-900 sm:text-5xl">{{ $heading }}</h1>
        <p class="mt-3 max-w-2xl text-ink-600">{{ $intro }}</p>
        <p class="mt-4 text-sm text-ink-500">{{ __('Pronađeno') }} <span class="font-semibold text-ink-800">{{ $photographers->total() }}</span> {{ __('profesionalaca') }}</p>
    </section>

    <section class="container-px mt-8 pb-12">
        @if ($photographers->isEmpty())
            <div class="rounded-2xl border border-dashed border-ink-200 p-12 text-center">
                <p class="font-serif text-xl text-ink-900">{{ __('Trenutno nema profesionalaca za ovaj kriterij') }}</p>
                <p class="mt-2 text-sm text-ink-500">{{ __('Pogledajte druge gradove ili kategorije.') }}</p>
                <a href="{{ localized_route('search') }}" class="btn-primary mt-4">{{ __('Otvori pretragu') }}</a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($photographers as $photographer)
                    <x-photographer-card :photographer="$photographer" />
                @endforeach
            </div>
            <div class="mt-8">{{ $photographers->links() }}</div>
        @endif
    </section>

    @if ($relatedTitle && $relatedLinks->isNotEmpty())
        <section class="container-px pb-16">
            <h2 class="font-serif text-2xl text-ink-900">{{ $relatedTitle }}</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($relatedLinks as $link)
                    <a href="{{ $link['url'] }}" class="pill">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </section>
    @endif

    @if (! empty($seoText) || ! empty($faqs))
        <section class="container-px grid grid-cols-1 gap-10 pb-16 lg:grid-cols-2">
            @if (! empty($seoText))
                <div>
                    <h2 class="font-serif text-2xl text-ink-900">{{ __('Pronađite pravog profesionalca za ovu lokaciju') }}</h2>
                    <p class="mt-4 leading-relaxed text-ink-600">{{ $seoText }}</p>
                </div>
            @endif
            @if (! empty($faqs))
                <div>
                    <h2 class="font-serif text-2xl text-ink-900">{{ __('Česta pitanja') }}</h2>
                    <div class="mt-4 divide-y divide-ink-100 border-y border-ink-100">
                        @foreach ($faqs as $faq)
                            <div class="py-4">
                                <h3 class="font-medium text-ink-900">{{ $faq['q'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $faq['a'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif
@endsection
