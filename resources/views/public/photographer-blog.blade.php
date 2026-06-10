@extends('layouts.app')

@section('content')
    <article class="container-px max-w-3xl pt-10 pb-16">
        <a href="{{ localized_route('photographer.show', $photographer->slug) }}" class="text-sm text-ink-500 hover:text-ink-900">&larr; {{ $photographer->display_name }}</a>

        @if ($article->category)
            <p class="eyebrow mt-6">{{ $article->category->name }}@if ($article->city) · {{ $article->city->name }}@endif</p>
        @endif
        <h1 class="mt-2 font-serif text-4xl leading-tight text-ink-900 sm:text-5xl">{{ $article->title }}</h1>
        <p class="mt-3 text-sm text-ink-400">
            {{ $photographer->display_name }}@if ($article->published_at) · {{ $article->published_at->translatedFormat('d. F Y.') }}@endif
        </p>

        @if ($article->featured_image)
            <img src="{{ media_url($article->featured_image) }}" alt="{{ $article->title }}" class="mt-6 aspect-[16/9] w-full rounded-2xl object-cover">
        @endif

        <div class="prose prose-ink mt-8 max-w-none text-ink-700 [&_p]:mt-4">
            {!! safe_public_html($article->content) !!}
        </div>

        @if ($article->images->isNotEmpty())
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($article->images as $img)
                    <div class="aspect-square overflow-hidden rounded-xl bg-ink-100">
                        <img src="{{ media_url($img->image_path) }}" alt="{{ $img->alt_text }}" loading="lazy" class="h-full w-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </article>
@endsection
