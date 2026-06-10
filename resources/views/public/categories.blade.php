@extends('layouts.app')

@section('content')
    <section class="container-px pt-10">
        <p class="eyebrow">{{ __('Kategorije') }}</p>
        <h1 class="mt-2 font-serif text-4xl text-ink-900 sm:text-5xl">{{ __('Pretražite po vrsti događaja') }}</h1>
        <p class="mt-3 max-w-2xl text-ink-600">{{ __('Od romantičnih vjenčanja do dinamičnih komercijalnih spotova — pronađite specijalistu za vaš projekat.') }}</p>
    </section>

    <section class="container-px mt-8 pb-16">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $category)
                <x-category-card :category="$category" :count="$category->photographers_count" />
            @endforeach
        </div>
    </section>
@endsection
