@extends('layouts.app')

@section('content')
    <article class="container-px pt-10 pb-16">
        <a href="{{ localized_route('photographer.show', $photographer->slug) }}" class="text-sm text-ink-500 hover:text-ink-900">&larr; {{ $photographer->display_name }}</a>

        <header class="mt-6 max-w-3xl">
            @if ($album->category)
                <p class="eyebrow">{{ $album->category->name }}</p>
            @endif
            <h1 class="mt-2 font-serif text-4xl text-ink-900 sm:text-5xl">{{ $album->title }}</h1>
            <p class="mt-3 text-ink-600">
                {{ __('Portfolio album fotografa :name', ['name' => $photographer->display_name]) }}@if ($photographer->primaryCity) {{ __('iz mjesta :city', ['city' => $photographer->primaryCity->name]) }}@endif.
            </p>
        </header>

        @if ($album->images->isNotEmpty())
            <section class="mt-8">
                <h2 class="font-serif text-2xl text-ink-900">{{ __('Fotografije') }}</h2>
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($album->images as $image)
                        <a href="{{ media_url($image->image_path) }}" target="_blank" rel="noopener" class="aspect-square overflow-hidden rounded-xl bg-ink-100">
                            <img src="{{ media_url($image->image_path) }}" alt="{{ $image->alt_text }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 hover:scale-105">
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($album->videos->isNotEmpty())
            <section class="mt-10">
                <h2 class="font-serif text-2xl text-ink-900">Video</h2>
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($album->videos as $video)
                        <article class="overflow-hidden rounded-2xl border border-ink-100 bg-white">
                            <div class="aspect-video bg-ink-900">
                                <iframe
                                    src="{{ $video->embedUrl() }}"
                                    title="{{ $video->title ?: 'Video portfolio' }}"
                                    class="h-full w-full"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen></iframe>
                            </div>
                            @if ($video->title)
                                <h3 class="p-3 font-serif text-base text-ink-900">{{ $video->title }}</h3>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
