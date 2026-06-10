<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        @foreach ($url['alternates'] ?? [] as $hreflang => $alternateUrl)
        <xhtml:link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $alternateUrl }}" />
        @endforeach
        @isset($url['lastmod'])<lastmod>{{ $url['lastmod'] }}</lastmod>@endisset
    </url>
@endforeach
</urlset>
