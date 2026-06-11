@extends('layouts.app')

@if ($isOwnerPreview ?? false)
    <div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900">
        Pregled vašeg profila. Profil još nije javno objavljen i vidljiv je samo vama.
    </div>
@endif

@php
    $allImages = $photographer->albums
        ->flatMap(fn ($a) => $a->images->map(fn ($img) => ['img' => $img, 'cat' => $a->category?->name, 'cat_slug' => $a->category?->slug]))
        ->values()
        ->map(fn ($item, $index) => $item + ['index' => $index]);
    $allVideos = $photographer->albums
        ->flatMap(fn ($a) => $a->videos->map(fn ($video) => ['video' => $video, 'cat' => $a->category?->name, 'cat_slug' => $a->category?->slug]))
        ->values();
    $lightboxImages = $allImages->map(fn ($item) => [
        'src' => media_url($item['img']->image_path),
        'alt' => $item['img']->alt_text,
    ])->values();
    $imageIndexes = $allImages->pluck('index', 'img.id');
    $heroImages = $allImages->take(5);
    $photoCount = $allImages->count();
    $videoCount = $allVideos->count();
    $albumCategories = $photographer->albums->pluck('category')->filter()->unique('id')->values();
    $social = $photographer->socialLinks;
    $today = now();
    $showHeroImages = $photographer->service_type !== \App\Enums\ServiceType::Videographer;
    $initialPortfolioLimit = 8;
@endphp

@section('content')
<div class="w-full min-w-0 overflow-x-hidden" x-data="{
    items: {{ Illuminate\Support\Js::from($lightboxImages) }},
    open: false,
    index: 0,
    touchStartX: 0,
    openLightbox(i) {
        if (! this.items.length) return;
        this.index = i;
        this.open = true;
        document.body.classList.add('overflow-hidden');
    },
    closeLightbox() {
        this.open = false;
        document.body.classList.remove('overflow-hidden');
    },
    previousImage() {
        if (! this.items.length) return;
        this.index = (this.index + this.items.length - 1) % this.items.length;
    },
    nextImage() {
        if (! this.items.length) return;
        this.index = (this.index + 1) % this.items.length;
    },
    handleTouchEnd(event) {
        const delta = event.changedTouches[0].clientX - this.touchStartX;
        if (Math.abs(delta) < 40) return;
        delta > 0 ? this.previousImage() : this.nextImage();
    },
}" @keydown.window.escape="closeLightbox()" @keydown.window.arrow-left="previousImage()" @keydown.window.arrow-right="nextImage()">
    {{-- Hero masonry --}}
    @if ($showHeroImages && $heroImages->isNotEmpty())
        <section class="container-px pt-6" data-profile-hero>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:grid-rows-2">
                <button type="button" @click="openLightbox({{ $heroImages[0]['index'] }})" class="col-span-2 row-span-2 aspect-square overflow-hidden rounded-2xl text-left sm:aspect-auto">
                    <img src="{{ media_url($heroImages[0]['img']->image_path) }}" alt="{{ $heroImages[0]['img']->alt_text }}" class="h-full w-full object-cover">
                </button>
                @foreach ($heroImages->slice(1, 4) as $i => $item)
                    <button type="button" @click="openLightbox({{ $item['index'] }})" class="relative aspect-square overflow-hidden rounded-2xl text-left sm:aspect-auto">
                        <img src="{{ media_url($item['img']->image_path) }}" alt="{{ $item['img']->alt_text }}" class="h-full w-full object-cover">
                        @if ($loop->last && $photoCount > 5)
                            <div class="absolute inset-0 flex items-center justify-center bg-ink-900/55 text-sm font-medium text-white">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                                    {{ $photoCount }} {{ __('fotografija') }}
                                </span>
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Profile card --}}
    <section class="container-px mt-6">
        <div class="rounded-3xl border border-ink-100 bg-white p-5 shadow-sm sm:p-8">
            <div class="flex min-w-0 flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex min-w-0 items-start gap-4 sm:gap-5">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-ink-100 sm:h-24 sm:w-24">
                        @if ($photographer->profile_image)
                            <img src="{{ media_url($photographer->profile_image) }}" alt="{{ $photographer->display_name }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="hidden sm:inline-flex"><x-availability-badge :available="$photographer->isAvailableOn($today->toDateString())" /></span>
                            <span class="rounded-full bg-ink-900 px-2.5 py-1 text-xs font-medium text-white">{{ $photographer->service_type->label() }}</span>
                            <span class="rounded-full bg-ink-50 px-2.5 py-1 text-xs font-medium text-ink-600">{{ $photographer->profile_type->label() }}</span>
                        </div>
                        <h1 class="wrap-anywhere mt-2 font-serif text-2xl leading-tight text-ink-900 sm:text-4xl">{{ $photographer->display_name }}</h1>
                        <p class="mt-1 hidden items-center gap-1.5 text-sm text-ink-500 sm:flex">
                            <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-5.2-7-11a7 7 0 1 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            {{ $photographer->primaryCity?->name }}@if ($photographer->experience_years) · {{ __(':count godina iskustva', ['count' => $photographer->experience_years]) }} @endif
                        </p>
                        <div class="mt-3 hidden flex-wrap gap-1.5 sm:flex">
                            @foreach ($photographer->categories as $category)
                                <a href="{{ localized_route('category.show', $category->slug) }}" class="pill">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="min-w-0 space-y-4 sm:hidden">
                    <div class="wrap-anywhere flex min-w-0 flex-wrap items-center gap-2 text-sm text-ink-600">
                        <x-availability-badge :available="$photographer->isAvailableOn($today->toDateString())" />
                        @if ($photographer->primaryCity)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-5.2-7-11a7 7 0 1 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                {{ $photographer->primaryCity->name }}
                            </span>
                        @endif
                        @if ($photographer->experience_years)
                            <span>{{ __(':count godina iskustva', ['count' => $photographer->experience_years]) }}</span>
                        @endif
                    </div>

                    @if ($photographer->categories->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($photographer->categories as $category)
                                <a href="{{ localized_route('category.show', $category->slug) }}" class="pill">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-col gap-2">
                        @if ($photographer->phone)
                            <a href="tel:{{ $photographer->phone }}" class="btn-primary justify-center">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 4h3l2 5-2 1a11 11 0 0 0 5 5l1-2 5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>
                                Pozovi
                            </a>
                        @endif
                        @if ($photographer->public_email)
                            <a href="mailto:{{ $photographer->public_email }}" class="btn-outline justify-center">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                                E-mail
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @include('public.partials.social-icons', ['social' => $social, 'website' => $photographer->website])
                    </div>
                </div>

                <div class="hidden min-w-0 flex-wrap items-center gap-2 sm:flex">
                    @if ($photographer->phone)
                        <a href="tel:{{ $photographer->phone }}" class="btn-primary">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 4h3l2 5-2 1a11 11 0 0 0 5 5l1-2 5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>
                            Pozovi
                        </a>
                    @endif
                    @if ($photographer->public_email)
                        <a href="mailto:{{ $photographer->public_email }}" class="btn-outline">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            E-mail
                        </a>
                    @endif
                    @include('public.partials.social-icons', ['social' => $social, 'website' => $photographer->website])
                </div>
            </div>
        </div>
    </section>

    <section class="container-px mt-10 grid grid-cols-1 gap-10 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="min-w-0">
            {{-- About --}}
            <h2 class="font-serif text-2xl text-ink-900">{{ __('O fotografu') }}</h2>
            <p class="wrap-anywhere mt-3 whitespace-pre-line leading-relaxed text-ink-600">{{ $photographer->about }}</p>

            {{-- Portfolio --}}
            @if ($allImages->isNotEmpty() || $allVideos->isNotEmpty())
                <div class="mt-10" x-data="{
                    filter: 'all',
                    visibleCount: {{ $initialPortfolioLimit }},
                    step: {{ $initialPortfolioLimit }},
                    init() {
                        this.step = this.pageSize();
                        this.visibleCount = this.step;
                        this.$nextTick(() => this.hydrateMedia());
                        window.addEventListener('resize', () => {
                            const nextStep = this.pageSize();
                            if (nextStep === this.step || this.visibleCount > this.step) return;
                            this.step = nextStep;
                            this.visibleCount = nextStep;
                            this.$nextTick(() => this.hydrateMedia());
                        });
                    },
                    pageSize() {
                        if (window.innerWidth >= 1024) return 8;
                        if (window.innerWidth >= 640) return 6;
                        return 4;
                    },
                    setFilter(value) {
                        this.filter = value;
                        this.visibleCount = this.pageSize();
                        this.$nextTick(() => this.hydrateMedia());
                    },
                    matches(item) {
                        return this.filter === 'all' || item.dataset.category === this.filter;
                    },
                    filteredItems() {
                        return Array.from(this.$root.querySelectorAll('[data-portfolio-item]')).filter((item) => this.matches(item));
                    },
                    visibleItems() {
                        return this.filteredItems().slice(0, this.visibleCount);
                    },
                    isVisible(item) {
                        return this.visibleItems().includes(item);
                    },
                    hasMore() {
                        return this.filteredItems().length > this.visibleCount;
                    },
                    remainingCount() {
                        return Math.max(this.filteredItems().length - this.visibleCount, 0);
                    },
                    loadMore() {
                        this.visibleCount += this.step;
                        this.$nextTick(() => this.hydrateMedia());
                    },
                    hydrateMedia() {
                        this.visibleItems().forEach((item) => {
                            item.querySelectorAll('[data-src]').forEach((media) => {
                                if (! media.getAttribute('src')) {
                                    media.setAttribute('src', media.dataset.src);
                                }
                            });
                        });
                    },
                }">
                    <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="font-serif text-2xl text-ink-900">{{ __('Portfolio') }}</h2>
                        <span class="inline-flex min-w-0 flex-wrap items-center gap-1.5 text-sm text-ink-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                            {{ $photoCount }} {{ __('fotografija') }} @if ($videoCount) &middot; {{ $videoCount }} {{ __('video') }} @endif
                        </span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button @click="setFilter('all')" :class="filter === 'all' ? 'pill-active' : ''" class="pill">{{ __('Sve') }}</button>
                        @foreach ($albumCategories as $category)
                            <button @click="setFilter('{{ $category->slug }}')" :class="filter === '{{ $category->slug }}' ? 'pill-active' : ''" class="pill">{{ $category->name }}</button>
                        @endforeach
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($photographer->albums as $album)
                            <a href="{{ localized_route('photographer.portfolio.album', [$photographer->slug, $album->slug]) }}" class="pill">{{ $album->title }}</a>
                        @endforeach
                    </div>
                    @php $portfolioItemIndex = 0; @endphp
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($photographer->albums as $album)
                            @foreach ($album->images as $img)
                                @php
                                    $portfolioItemIndex++;
                                    $isInitiallyVisible = $portfolioItemIndex <= $initialPortfolioLimit;
                                    $imageUrl = media_url($img->image_path);
                                @endphp
                                <div data-portfolio-item
                                     data-category="{{ $album->category?->slug }}"
                                     x-show="isVisible($el)"
                                     @unless ($isInitiallyVisible) style="display: none;" @endunless
                                     class="aspect-square overflow-hidden rounded-xl bg-ink-100">
                                    <button type="button" @click="openLightbox({{ $imageIndexes[$img->id] ?? 0 }})" class="h-full w-full">
                                        <img data-portfolio-image
                                             data-src="{{ $imageUrl }}"
                                             @if ($isInitiallyVisible) src="{{ $imageUrl }}" @endif
                                             :src="isVisible($el.closest('[data-portfolio-item]')) ? $el.dataset.src : null"
                                             alt="{{ $img->alt_text }}"
                                             loading="lazy"
                                             decoding="async"
                                             data-portfolio-visible="{{ $isInitiallyVisible ? 'initial' : 'deferred' }}"
                                             class="h-full w-full object-cover transition duration-500 hover:scale-105">
                                    </button>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                    @if ($allVideos->isNotEmpty())
                        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ($photographer->albums as $album)
                                @foreach ($album->videos as $video)
                                    @php
                                        $portfolioItemIndex++;
                                        $isInitiallyVisible = $portfolioItemIndex <= $initialPortfolioLimit;
                                        $embedUrl = $video->embedUrl();
                                    @endphp
                                    <article data-portfolio-item
                                             data-category="{{ $album->category?->slug }}"
                                             x-show="isVisible($el)"
                                             @unless ($isInitiallyVisible) style="display: none;" @endunless
                                             class="overflow-hidden rounded-2xl border border-ink-100 bg-white">
                                        <div class="aspect-video bg-ink-900">
                                            <iframe
                                                data-src="{{ $embedUrl }}"
                                                @if ($isInitiallyVisible) src="{{ $embedUrl }}" @endif
                                                :src="isVisible($el.closest('[data-portfolio-item]')) ? $el.dataset.src : null"
                                                title="{{ $video->title ?: 'Video portfolio' }}"
                                                class="h-full w-full"
                                                loading="lazy"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen></iframe>
                                        </div>
                                        @if ($video->title || $album->category)
                                            <div class="p-3">
                                                @if ($video->title)
                                                    <h3 class="font-serif text-base text-ink-900">{{ $video->title }}</h3>
                                                @endif
                                                @if ($album->category)
                                                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-accent-600">{{ $album->category->name }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </article>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-6 flex justify-center" x-show="hasMore()" x-cloak>
                        <button type="button" @click="loadMore()" class="btn-outline">
                            Učitaj još
                            <span class="text-ink-400" x-text="'(' + remainingCount() + ')'"></span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Photographer blog --}}
            @if ($photographer->blogPosts->isNotEmpty())
                <div class="mt-12">
                    <h2 class="font-serif text-2xl text-ink-900">{{ __('Članci fotografa') }}</h2>
                    <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        @foreach ($photographer->blogPosts as $post)
                            <a href="{{ localized_route('photographer.blog.show', [$photographer->slug, $post->slug]) }}" class="group flex gap-4 rounded-2xl border border-ink-100 p-3 transition hover:shadow-sm">
                                <div class="h-20 w-24 shrink-0 overflow-hidden rounded-xl bg-ink-100">
                                    @if ($post->featured_image)<img src="{{ media_url($post->featured_image) }}" alt="{{ $post->title }}" class="h-full w-full object-cover">@endif
                                </div>
                                <div>
                                    <h3 class="font-serif text-base leading-snug text-ink-900 group-hover:text-accent-700">{{ $post->title }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs text-ink-500">{{ $post->excerpt }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar: availability + contact --}}
        <aside class="min-w-0 space-y-6 lg:sticky lg:top-20 lg:h-fit">
            <div class="rounded-2xl border border-ink-100 bg-white p-5">
                <h3 class="font-serif text-lg text-ink-900">{{ __('Dostupnost') }}</h3>
                <p class="mt-0.5 text-xs text-ink-400">{{ __('Naredne 4 sedmice') }}</p>
                <div class="mt-4 grid grid-cols-7 gap-1.5 text-center text-xs">
                    @foreach (['P','U','S','Č','P','S','N'] as $d)
                        <div class="font-medium text-ink-400">{{ $d }}</div>
                    @endforeach
                    @php $lead = ($calendar[0]['date']->dayOfWeekIso) - 1; @endphp
                    @for ($i = 0; $i < $lead; $i++)<div></div>@endfor
                    @foreach ($calendar as $day)
                        <div class="flex h-8 items-center justify-center rounded-lg text-xs font-medium
                            {{ $day['available'] ? 'bg-emerald-50 text-emerald-700' : 'bg-ink-50 text-ink-300 line-through' }}">
                            {{ $day['date']->day }}
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center gap-4 text-xs text-ink-500">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> {{ __('Dostupno') }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-ink-300"></span> {{ __('Zauzeto') }}</span>
                </div>
            </div>

            <div class="rounded-2xl border border-ink-100 bg-white p-5">
                <h3 class="font-serif text-lg text-ink-900">{{ __('Kontakt') }}</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    @if ($photographer->phone)
                        <li class="min-w-0"><a href="tel:{{ $photographer->phone }}" class="wrap-anywhere flex min-w-0 items-center gap-2.5 text-ink-700 hover:text-ink-900"><svg class="h-4 w-4 shrink-0 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 4h3l2 5-2 1a11 11 0 0 0 5 5l1-2 5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>{{ $photographer->phone }}</a></li>
                    @endif
                    @if ($photographer->public_email)
                        <li class="min-w-0"><a href="mailto:{{ $photographer->public_email }}" class="wrap-anywhere flex min-w-0 items-center gap-2.5 text-ink-700 hover:text-ink-900"><svg class="h-4 w-4 shrink-0 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>{{ $photographer->public_email }}</a></li>
                    @endif
                    @if ($social?->instagram)
                        <li class="min-w-0"><a href="{{ $social->instagram }}" target="_blank" rel="nofollow noopener" class="wrap-anywhere flex min-w-0 items-center gap-2.5 text-ink-700 hover:text-ink-900"><svg class="h-4 w-4 shrink-0 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>Instagram</a></li>
                    @endif
                    @if ($photographer->website)
                        <li class="min-w-0"><a href="{{ $photographer->website }}" target="_blank" rel="nofollow noopener" class="wrap-anywhere flex min-w-0 items-center gap-2.5 text-ink-700 hover:text-ink-900"><svg class="h-4 w-4 shrink-0 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18Z"/></svg>{{ __('Web stranica') }}</a></li>
                    @endif
                </ul>
            </div>
        </aside>
    </section>

    <div x-show="open" x-transition.opacity x-cloak class="fixed inset-0 z-50 bg-ink-900/95" style="display:none">
        <button type="button" @click="closeLightbox()" class="absolute right-4 top-4 z-10 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/20" aria-label="{{ __('Zatvori') }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
        </button>

        <button type="button" @click="previousImage()" class="absolute left-3 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/20 sm:block" aria-label="{{ __('Prethodna slika') }}">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        </button>

        <button type="button" @click="nextImage()" class="absolute right-3 top-1/2 z-10 hidden -translate-y-1/2 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/20 sm:block" aria-label="{{ __('Sljedeća slika') }}">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
        </button>

        <div class="flex h-full w-full items-center justify-center px-4 py-16"
             @touchstart="touchStartX = $event.changedTouches[0].clientX"
             @touchend="handleTouchEnd($event)">
            <img :src="items[index]?.src" :alt="items[index]?.alt || ''" class="max-h-full max-w-full rounded-xl object-contain shadow-2xl">
        </div>

        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-sm text-white">
            <span x-text="index + 1"></span> / <span x-text="items.length"></span>
        </div>
    </div>
</div>
@endsection
