@extends('layouts.app')

@section('content')
    <section class="container-px pt-10">
        <p class="eyebrow">Blog</p>
        <h1 class="mt-2 font-serif text-4xl text-ink-900 sm:text-5xl">Savjeti i inspiracija</h1>
        <p class="mt-3 max-w-2xl text-ink-600">Vodiči i savjeti za pronalazak pravog fotografa ili videografa za vaš događaj.</p>
    </section>

    <section class="container-px mt-8 pb-16">
        @if ($posts->isEmpty())
            <p class="text-ink-500">Još nema objavljenih članaka.</p>
        @else
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($posts as $post)
                    @include('public.partials.blog-card', ['post' => $post])
                @endforeach
            </div>
            <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </section>
@endsection
