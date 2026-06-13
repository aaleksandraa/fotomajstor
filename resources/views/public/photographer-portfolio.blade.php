<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050505">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @foreach ($images->take(4) as $image)
        <link rel="preload" as="image" href="{{ $image['src'] }}" crossorigin="anonymous">
    @endforeach
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { width: 100%; height: 100%; overflow: hidden; background: #050505; }
        [data-spherical-gallery] { position: fixed; inset: 0; color: #fff; background: #050505; }
        [data-spherical-gallery] canvas { display: block; width: 100%; height: 100%; cursor: grab; touch-action: none; -webkit-tap-highlight-color: transparent; }
        [data-spherical-gallery].is-dragging canvas { cursor: grabbing; }
        [data-spherical-gallery].is-hovering:not(.is-dragging) canvas { cursor: pointer; }
        .gallery-chrome { position: fixed; z-index: 10; display: flex; align-items: center; border: 1px solid rgba(255,255,255,.16); background: rgba(8,8,8,.62); box-shadow: 0 12px 40px rgba(0,0,0,.3); backdrop-filter: blur(18px); }
        .gallery-back { top: 18px; left: 18px; gap: 10px; max-width: calc(100vw - 100px); padding: 10px 15px 10px 11px; border-radius: 999px; color: #fff; font: 500 12px/1 Inter, sans-serif; text-decoration: none; }
        .gallery-back svg { width: 16px; height: 16px; }
        .gallery-back span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .gallery-count { top: 18px; right: 18px; padding: 11px 14px; border-radius: 999px; color: rgba(255,255,255,.72); font: 500 11px/1 Inter, sans-serif; letter-spacing: .08em; text-transform: uppercase; }
        .gallery-hint { bottom: 20px; left: 50%; gap: 9px; padding: 12px 16px; border-radius: 999px; transform: translateX(-50%); color: rgba(255,255,255,.72); font: 500 11px/1 Inter, sans-serif; white-space: nowrap; transition: opacity .5s ease, transform .5s ease; }
        .gallery-hint.is-hidden { opacity: 0; transform: translate(-50%, 12px); pointer-events: none; }
        .gallery-loader { position: fixed; inset: 0; z-index: 30; display: grid; place-content: center; gap: 13px; background: #050505; text-align: center; }
        .gallery-loader-mark { width: 42px; height: 42px; margin: 0 auto; border: 1px solid rgba(255,255,255,.18); border-top-color: #fff; border-radius: 50%; animation: gallery-spin 1s linear infinite; }
        .gallery-loader p { margin: 0; color: rgba(255,255,255,.6); font: 500 11px/1 Inter, sans-serif; letter-spacing: .09em; text-transform: uppercase; }
        .gallery-loader strong { color: #fff; font-weight: 500; }
        .gallery-modal { position: fixed; inset: 0; z-index: 20; display: none; place-items: center; padding: 56px 24px 24px; background: rgba(0,0,0,.9); backdrop-filter: blur(18px); }
        .gallery-modal.is-open { display: grid; }
        .gallery-modal img { display: block; max-width: min(92vw, 1500px); max-height: calc(100vh - 92px); border-radius: 4px; object-fit: contain; box-shadow: 0 30px 100px rgba(0,0,0,.55); }
        .gallery-close { position: absolute; top: 18px; right: 18px; display: grid; width: 42px; height: 42px; place-items: center; border: 1px solid rgba(255,255,255,.18); border-radius: 50%; background: rgba(10,10,10,.66); color: #fff; cursor: pointer; backdrop-filter: blur(14px); }
        .gallery-close svg { width: 18px; height: 18px; }
        @keyframes gallery-spin { to { transform: rotate(360deg); } }
        @media (max-width: 640px) {
            .gallery-back { top: 12px; left: 12px; max-width: calc(100vw - 92px); }
            .gallery-count { top: 12px; right: 12px; padding: 11px 12px; }
            .gallery-count span { display: none; }
            .gallery-hint { bottom: 14px; }
            .gallery-modal { padding: 68px 12px 16px; }
            .gallery-modal img { max-width: 100%; max-height: calc(100dvh - 84px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .gallery-hint { transition: none; }
            .gallery-loader-mark { animation-duration: 2s; }
        }
    </style>
</head>
<body>
    <main data-spherical-gallery>
        <canvas aria-label="{{ __('Interaktivni portfolio fotografa :name', ['name' => $photographer->display_name]) }}"></canvas>

        <a href="{{ localized_route('photographer.show', $photographer->slug) }}" class="gallery-chrome gallery-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
            <span>{{ $photographer->display_name }}</span>
        </a>

        <div class="gallery-chrome gallery-count">
            {{ $images->count() }} <span>{{ __('fotografija') }}</span>
        </div>

        <div class="gallery-chrome gallery-hint" data-gallery-hint>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 11V7a2 2 0 1 1 4 0v4-6a2 2 0 1 1 4 0v7-3a2 2 0 1 1 4 0v5a8 8 0 0 1-8 8h-1a8 8 0 0 1-7-4l-2-3a2 2 0 0 1 3-2l3 3"/></svg>
            {{ __('Povucite za istraživanje · kliknite za otvaranje') }}
        </div>

        <div class="gallery-loader" data-gallery-loader>
            <div class="gallery-loader-mark"></div>
            <p>{{ __('Učitavam portfolio') }} <strong data-gallery-progress>0 / {{ $images->count() }}</strong></p>
        </div>

        <div class="gallery-modal" data-gallery-modal aria-hidden="true">
            <button type="button" class="gallery-close" data-gallery-close aria-label="{{ __('Zatvori') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
            <img data-gallery-modal-image src="" alt="">
        </div>

        <script type="application/json" data-gallery-images>@json($images)</script>
    </main>
</body>
</html>
