<?php

namespace App\Support;

class LocalizedUrl
{
    public static function for(string $url, string $locale): string
    {
        $supported = array_keys(config('locales.supported', []));
        $default = config('locales.default', 'bs');
        $parts = parse_url($url);
        $path = '/'.ltrim($parts['path'] ?? '/', '/');
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn ($segment) => $segment !== ''));

        if ($segments && in_array($segments[0], $supported, true) && $segments[0] !== $default) {
            array_shift($segments);
        }

        if ($locale !== $default) {
            array_unshift($segments, $locale);
        }

        $localizedPath = '/'.implode('/', $segments);
        $localizedPath = $localizedPath === '/' ? '/' : rtrim($localizedPath, '/');
        $base = isset($parts['scheme'], $parts['host'])
            ? $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '')
            : url('/');
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        $localizedUrl = $localizedPath === '/'
            ? rtrim($base, '/')
            : rtrim($base, '/').$localizedPath;

        return $localizedUrl.$query.$fragment;
    }

    /** @return array<string, string> */
    public static function alternates(string $url, ?array $locales = null): array
    {
        $supported = collect(config('locales.supported', []));

        if ($locales !== null) {
            $supported = $supported->only($locales);
        }

        return $supported
            ->mapWithKeys(fn (array $meta, string $locale) => [
                $meta['hreflang'] ?? $locale => self::for($url, $locale),
            ])
            ->put('x-default', self::for($url, config('locales.default', 'bs')))
            ->all();
    }
}
