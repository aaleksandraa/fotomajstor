@extends('layouts.app')

@section('content')
    <article class="container-px py-12 sm:py-16">
        <header class="max-w-3xl">
            <p class="eyebrow">{{ __('Pravne informacije') }}</p>
            <h1 class="mt-3 font-serif text-4xl text-ink-900 sm:text-5xl">{{ $title }}</h1>
            <p class="mt-4 text-base leading-relaxed text-ink-600">{{ $intro }}</p>
            <p class="mt-3 text-sm text-ink-400">{{ __('Primjenjuje se od: :date', ['date' => config('legal.effective_date')]) }}</p>
        </header>

        <div class="legal-content mt-10 max-w-4xl">
            @include($partial)
        </div>
    </article>
@endsection
