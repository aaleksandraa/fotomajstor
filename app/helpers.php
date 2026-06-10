<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (! function_exists('media_url')) {
    /**
     * Resolve a stored media path or a remote placeholder URL to a usable src.
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (blank($path)) {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('placeholder_image')) {
    /**
     * Deterministic placeholder image used for seed/demo content.
     */
    function placeholder_image(string $seed, int $width = 800, int $height = 600): string
    {
        return 'https://picsum.photos/seed/'.urlencode($seed).'/'.$width.'/'.$height;
    }
}

if (! function_exists('localized_route')) {
    /**
     * Generate the current language version of a named public route.
     */
    function localized_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $locale = app()->getLocale();
        $default = config('locales.default', 'bs');
        $localizedName = $locale !== $default ? $locale.'.'.$name : $name;

        return route(Route::has($localizedName) ? $localizedName : $name, $parameters, $absolute);
    }
}

if (! function_exists('paginated_canonical')) {
    /**
     * Keep paginated collections self-canonical while excluding other filters.
     */
    function paginated_canonical(string $url, mixed $page = null): string
    {
        $page = (int) ($page ?? request()->query('page', 1));

        return $page > 1 ? $url.'?page='.$page : $url;
    }
}

if (! function_exists('seo_brand_title')) {
    function seo_brand_title(string $title): string
    {
        return preg_replace('/\s*\|.*$/u', ' | '.config('app.name'), $title) ?: $title;
    }
}

if (! function_exists('safe_public_html')) {
    /**
     * Sanitize rich text that is rendered on public pages.
     */
    function safe_public_html(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $allowedTags = [
            'a', 'blockquote', 'br', 'code', 'div', 'em', 'figcaption', 'figure',
            'h2', 'h3', 'h4', 'hr', 'img', 'li', 'ol', 'p', 'pre', 'span',
            'strong', 'ul',
        ];
        $allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
            '*' => ['class'],
        ];
        $blockedTags = ['button', 'embed', 'form', 'iframe', 'input', 'math', 'object', 'script', 'select', 'style', 'svg', 'textarea'];
        $uriAttributes = ['href', 'src'];
        $allowedSchemes = ['http', 'https', 'mailto', 'tel'];

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $wrapped = '<div>'.mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8').'</div>';

        if (! $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return strip_tags($html, '<p><br><strong><em><ul><ol><li><blockquote><a><h2><h3><h4><pre><code>');
        }

        $cleanNode = function (DOMNode $node) use (&$cleanNode, $allowedTags, $allowedAttributes, $uriAttributes, $allowedSchemes, $blockedTags): void {
            if ($node instanceof DOMElement) {
                $tag = strtolower($node->tagName);

                if (in_array($tag, $blockedTags, true)) {
                    $node->parentNode?->removeChild($node);

                    return;
                }

                if (! in_array($tag, $allowedTags, true)) {
                    $fragment = $node->ownerDocument->createDocumentFragment();
                    while ($node->firstChild) {
                        $fragment->appendChild($node->firstChild);
                    }
                    $node->parentNode?->replaceChild($fragment, $node);

                    return;
                }

                foreach (iterator_to_array($node->attributes) as $attribute) {
                    $name = strtolower($attribute->name);
                    $value = trim($attribute->value);
                    $allowedForTag = array_merge($allowedAttributes[$tag] ?? [], $allowedAttributes['*'] ?? []);

                    if (str_starts_with($name, 'on') || ! in_array($name, $allowedForTag, true)) {
                        $node->removeAttributeNode($attribute);
                        continue;
                    }

                    if (in_array($name, $uriAttributes, true)) {
                        $scheme = parse_url($value, PHP_URL_SCHEME);
                        if ($scheme !== null && ! in_array(strtolower($scheme), $allowedSchemes, true)) {
                            $node->removeAttributeNode($attribute);
                            continue;
                        }
                    }

                    if ($tag === 'a' && $name === 'target' && $value === '_blank') {
                        $node->setAttribute('rel', 'nofollow noopener noreferrer');
                    }
                }

                if ($tag === 'img' && ! $node->hasAttribute('loading')) {
                    $node->setAttribute('loading', 'lazy');
                }
            }

            foreach (iterator_to_array($node->childNodes) as $child) {
                $cleanNode($child);
            }
        };

        $cleanNode($document->documentElement);

        $output = '';
        foreach ($document->documentElement->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $output;
    }
}

if (! function_exists('safe_public_text')) {
    /**
     * Convert public rich text to safe plain text for meta descriptions and summaries.
     */
    function safe_public_text(?string $html, int $limit = 155): string
    {
        return Str::of(strip_tags(safe_public_html($html)))
            ->squish()
            ->limit($limit)
            ->toString();
    }
}
