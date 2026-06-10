@extends('layouts.app')

@section('content')
    <article class="container-px max-w-3xl pt-10 pb-16">
        <a href="{{ localized_route('blog.index') }}" class="text-sm text-ink-500 hover:text-ink-900">&larr; Nazad na blog</a>

        @if ($post->category)
            <p class="eyebrow mt-6">{{ $post->category->name }}</p>
        @endif
        <h1 class="mt-2 font-serif text-4xl leading-tight text-ink-900 sm:text-5xl">{{ $post->title }}</h1>
        <p class="mt-3 text-sm text-ink-400">
            @if ($post->author){{ $post->author->name }} · @endif
            @if ($post->published_at){{ $post->published_at->translatedFormat('d. F Y.') }}@endif
        </p>

        @if ($post->featured_image)
            <img src="{{ media_url($post->featured_image) }}" alt="{{ $post->title }}" class="mt-6 aspect-[16/9] w-full rounded-2xl object-cover">
        @endif

        <div class="prose prose-ink mt-8 max-w-none text-ink-700 [&_h2]:font-serif [&_h2]:text-2xl [&_h3]:font-serif [&_h3]:text-xl [&_p]:mt-4 [&_h2]:mt-8 [&_h3]:mt-6">
            {!! safe_public_html($post->content) !!}
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="container-px pb-16">
            <h2 class="font-serif text-2xl text-ink-900">Povezani članci</h2>
            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($related as $post)
                    @include('public.partials.blog-card', ['post' => $post])
                @endforeach
            </div>
        </section>
    @endif
@endsection
