<?php

namespace App\Observers;

use App\Support\SitemapCache;

class SeoContentObserver
{
    public function saved(): void
    {
        SitemapCache::invalidate();
    }

    public function deleted(): void
    {
        SitemapCache::invalidate();
    }

    public function restored(): void
    {
        SitemapCache::invalidate();
    }
}
