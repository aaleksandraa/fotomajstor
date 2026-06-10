@php
    $siteName = config('app.name');
    $title = __($seo['title'] ?? $siteName);
    $description = __($seo['description'] ?? 'SEO-first direktorijum fotografa i videografa za BiH, Srbiju, Hrvatsku i regiju.');
    $canonical = \App\Support\LocalizedUrl::for($seo['canonical'] ?? url()->current(), $seo['canonicalLocale'] ?? app()->getLocale());
    $alternates = \App\Support\LocalizedUrl::alternates($canonical, $seo['locales'] ?? null);
    $ogType = $seo['type'] ?? 'website';
    $image = $seo['image'] ?? asset('og-default.svg');
    $robots = $seo['robots'] ?? 'index, follow';
    $jsonLd = $seo['jsonLd'] ?? [];
    if (! empty($jsonLd) && array_is_list($jsonLd) === false) {
        $jsonLd = [$jsonLd];
    }
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
@foreach ($alternates as $hreflang => $alternateUrl)
<link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $alternateUrl }}">
@endforeach

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

@foreach ($jsonLd as $schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
