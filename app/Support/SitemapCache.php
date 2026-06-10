<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class SitemapCache
{
    public const VERSION_KEY = 'sitemap.version';

    public static function key(string $locale, string $section): string
    {
        return 'sitemap.'.Cache::get(self::VERSION_KEY, 1).'.'.$locale.'.'.$section;
    }

    public static function invalidate(): void
    {
        Cache::increment(self::VERSION_KEY);
    }
}
