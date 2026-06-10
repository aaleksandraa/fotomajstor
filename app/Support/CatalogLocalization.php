<?php

namespace App\Support;

class CatalogLocalization
{
    public static function name(string $type, string $slug, string $fallback): string
    {
        $key = "catalog.{$type}.{$slug}";
        $translated = __($key);

        return $translated === $key ? $fallback : $translated;
    }
}
