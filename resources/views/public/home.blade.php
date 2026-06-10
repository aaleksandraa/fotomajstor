@extends('layouts.app')

@section('content')
    {{-- Hero --}}
    <section class="relative z-30 overflow-visible">
        <div class="absolute inset-0">
            <img src="{{ placeholder_image('hero-photography', 2000, 1200) }}" alt="" width="2000" height="1200" fetchpriority="high" class="h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-white/85 via-white/70 to-white"></div>
        </div>

        <div class="container-px relative z-10 pt-16 pb-10 text-center sm:pt-24">
            <p class="eyebrow">FotoMreža</p>
            <h1 class="mx-auto mt-3 max-w-4xl break-words font-serif text-3xl leading-[1.08] text-ink-900 sm:text-5xl lg:text-6xl">
                {{ __('Pronađite fotografa ili videografa za vaš događaj') }}
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-ink-600 sm:text-lg">
                {{ __('Od romantičnih vjenčanja do dinamičnih komercijalnih spotova — pretražite provjerene profesionalce po gradu, kategoriji i dostupnosti.') }}
            </p>

            <div class="mx-auto mt-8 w-full">
                <x-search-bar :filters="[]" />
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                <span class="text-sm text-ink-500">{{ __('Popularno:') }}</span>
                @foreach ($popularSearches as $search)
                    <a href="{{ $search['url'] }}" class="pill">{{ __($search['label']) }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Popular categories --}}
    <section class="container-px py-10">
        <div class="flex items-end justify-between">
            <div>
                <p class="eyebrow">{{ __('Kategorije') }}</p>
                <h2 class="mt-2 font-serif text-3xl text-ink-900">{{ __('Pretražite po vrsti događaja') }}</h2>
            </div>
            <a href="{{ localized_route('categories.index') }}" class="hidden text-sm font-medium text-ink-600 hover:text-ink-900 sm:block">{{ __('Sve kategorije') }} &rarr;</a>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($popularCategories as $category)
                <x-category-card :category="$category" :count="$category->photographers_count" />
            @endforeach
        </div>
    </section>

    {{-- Featured photographers --}}
    <section class="container-px py-10">
        <div class="flex items-end justify-between">
            <div>
                <p class="eyebrow">{{ __('Istaknuti') }}</p>
                <h2 class="mt-2 font-serif text-3xl text-ink-900">{{ __('Istaknuti fotografi i videografi') }}</h2>
            </div>
            <a href="{{ localized_route('search') }}" class="hidden text-sm font-medium text-ink-600 hover:text-ink-900 sm:block">{{ __('Pogledaj sve') }} &rarr;</a>
        </div>
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $photographer)
                <x-photographer-card :photographer="$photographer" />
            @endforeach
        </div>
    </section>

    {{-- Latest work masonry --}}
    @if ($latestWork->isNotEmpty())
        <section class="container-px py-10">
            <p class="eyebrow">{{ __('Portfolio') }}</p>
            <h2 class="mt-2 font-serif text-3xl text-ink-900">{{ __('Najnoviji radovi') }}</h2>
            <div class="mt-6 columns-2 gap-4 sm:columns-3 lg:columns-4 [&>*]:mb-4">
                @foreach ($latestWork as $image)
                    <a href="{{ localized_route('photographer.show', $image->album->photographerProfile->slug) }}" class="group block overflow-hidden rounded-xl">
                        <img src="{{ media_url($image->image_path) }}" alt="{{ $image->alt_text }}" loading="lazy"
                             class="w-full rounded-xl object-cover transition duration-500 group-hover:scale-105">
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Popular cities --}}
    <section class="container-px py-10">
        <p class="eyebrow">{{ __('Lokacije') }}</p>
        <h2 class="mt-2 font-serif text-3xl text-ink-900">{{ __('Popularni gradovi') }}</h2>
        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($popularCities as $city)
                <a href="{{ localized_route('landing.country.city', [$city->country->slug, $city->slug]) }}" class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-sm text-ink-700 transition hover:border-ink-400">
                    <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-5.2-7-11a7 7 0 1 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    {{ $city->name }}
                    <span class="text-ink-400">{{ $city->photographers_count }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Blog --}}
    @if ($blogPosts->isNotEmpty())
        <section class="container-px py-10">
            <div class="flex items-end justify-between">
                <div>
                    <p class="eyebrow">{{ __('Blog') }}</p>
                    <h2 class="mt-2 font-serif text-3xl text-ink-900">{{ __('Savjeti i inspiracija') }}</h2>
                </div>
                <a href="{{ localized_route('blog.index') }}" class="hidden text-sm font-medium text-ink-600 hover:text-ink-900 sm:block">{{ __('Svi članci') }} &rarr;</a>
            </div>
            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($blogPosts as $post)
                    @include('public.partials.blog-card', ['post' => $post])
                @endforeach
            </div>
        </section>
    @endif

    {{-- SEO text + FAQ --}}
    <section class="container-px py-12">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
            <div class="prose max-w-none">
                <h2 class="font-serif text-3xl text-ink-900">{{ __('Pronađite pravog fotografa u svom gradu') }}</h2>
                <p class="mt-4 text-ink-600">
                    {{ __('FotoMreža je profesionalni katalog fotografa i videografa za Bosnu i Hercegovinu, Srbiju, Hrvatsku, Sloveniju i Crnu Goru. Pretražite po lokaciji, kategoriji i datumu i kontaktirajte profesionalca direktno.') }}
                </p>
                <p class="mt-4 text-ink-600">
                    {{ __('Bez provizija, bez posrednika i bez skrivenih troškova. Svaki profil sadrži portfolio, kalendar dostupnosti i kontakt podatke.') }}
                </p>
            </div>

            <div x-data="{ open: 0 }">
                <p class="eyebrow">{{ __('Česta pitanja') }}</p>
                <h2 class="mt-2 font-serif text-3xl text-ink-900">FAQ</h2>
                <div class="mt-6 divide-y divide-ink-100 border-y border-ink-100">
                    @foreach ($faqs as $i => $faq)
                        <div class="py-4">
                            <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between text-left">
                                <span class="font-medium text-ink-900">{{ __($faq['q']) }}</span>
                                <svg class="h-5 w-5 text-ink-400 transition" :class="open === {{ $i }} ? 'rotate-45' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                            </button>
                            <div x-show="open === {{ $i }}" x-collapse style="display:none">
                                <p class="pt-3 text-sm text-ink-600">{{ __($faq['a']) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
