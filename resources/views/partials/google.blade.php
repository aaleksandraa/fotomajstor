@php
    $googleAnalyticsId = trim((string) config('services.google.analytics_id'));
    $googleSiteVerification = trim((string) config('services.google.site_verification'));
    $googleAnalyticsEnabled = preg_match('/^G-[A-Z0-9]+$/i', $googleAnalyticsId) === 1;
@endphp

@if ($googleSiteVerification !== '')
<meta name="google-site-verification" content="{{ $googleSiteVerification }}">
@endif

@if ($googleAnalyticsEnabled)
<script>
    window.fotoMajstorConsentKey = {{ Illuminate\Support\Js::from(config('legal.consent_storage_key')) }};
    window.fotoMajstorAnalyticsId = {{ Illuminate\Support\Js::from($googleAnalyticsId) }};
    window.fotoMajstorLoadAnalytics = function () {
        if (window.fotoMajstorAnalyticsLoaded) return;

        window.fotoMajstorAnalyticsLoaded = true;
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('consent', 'update', { analytics_storage: 'granted' });
        window.gtag('config', window.fotoMajstorAnalyticsId, { send_page_view: true });

        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(window.fotoMajstorAnalyticsId);
        document.head.appendChild(script);
    };

    try {
        if (window.localStorage.getItem(window.fotoMajstorConsentKey) === 'analytics') {
            window.fotoMajstorLoadAnalytics();
        }
    } catch (error) {
        // Analytics remains disabled when consent storage is unavailable.
    }
</script>
@endif
