@php
    $googleAnalyticsId = trim((string) config('services.google.analytics_id'));
    $googleSiteVerification = trim((string) config('services.google.site_verification'));
    $googleAnalyticsEnabled = preg_match('/^G-[A-Z0-9]+$/i', $googleAnalyticsId) === 1;
@endphp

@if ($googleSiteVerification !== '')
<meta name="google-site-verification" content="{{ $googleSiteVerification }}">
@endif

@if ($googleAnalyticsEnabled)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ rawurlencode($googleAnalyticsId) }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', {{ Illuminate\Support\Js::from($googleAnalyticsId) }}, {
        send_page_view: true,
    });
</script>
@endif
