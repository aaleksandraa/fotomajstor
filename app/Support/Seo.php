<?php

namespace App\Support;

class Seo
{
    /**
     * Build a BreadcrumbList JSON-LD schema.
     *
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    public static function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
            'logo' => asset('favicon.svg'),
            'image' => asset('fotoMajstor.jpg'),
        ];
    }

    /** @return array<string, mixed> */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => url('/'),
            'image' => asset('fotoMajstor.jpg'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => localized_route('search').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * LocalBusiness / ProfessionalService schema for a photographer profile.
     *
     * @return array<string, mixed>
     */
    public static function professionalService(\App\Models\PhotographerProfile $profile): array
    {
        $portfolioImages = collect();
        if ($profile->relationLoaded('albums')) {
            $portfolioImages = $profile->albums
                ->flatMap(fn ($album) => $album->relationLoaded('images') ? $album->images->pluck('image_path') : collect())
                ->filter()
                ->take(8)
                ->map(fn ($path) => media_url($path));
        }

        $imageUrls = collect([$profile->cover_image, $profile->profile_image])
            ->filter()
            ->map(fn ($path) => media_url($path))
            ->merge($portfolioImages)
            ->filter()
            ->unique()
            ->values();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => $profile->display_name,
            'url' => localized_route('photographer.show', $profile->slug),
            'description' => safe_public_text(str($profile->about)->limit(300)->value()),
            'serviceType' => $profile->service_type->label(),
        ];

        if ($imageUrls->isNotEmpty()) {
            $schema['image'] = $imageUrls->count() === 1 ? $imageUrls->first() : $imageUrls->all();
        }
        if ($profile->phone) {
            $schema['telephone'] = $profile->phone;
        }
        if ($profile->public_email) {
            $schema['email'] = $profile->public_email;
        }
        if ($profile->primaryCity) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $profile->primaryCity->name,
                'addressCountry' => $profile->primaryCountry?->code ?? 'BA',
            ];
        }
        if ($profile->relationLoaded('categories') && $profile->categories->isNotEmpty()) {
            $schema['knowsAbout'] = $profile->categories->pluck('name')->values()->all();
        }
        if ($profile->relationLoaded('cities') && $profile->cities->isNotEmpty()) {
            $schema['areaServed'] = $profile->cities->take(8)->map(fn ($city) => [
                '@type' => 'City',
                'name' => $city->name,
                'addressCountry' => $city->country?->code,
            ])->values()->all();
        }
        if ($profile->relationLoaded('socialLinks') && $profile->socialLinks) {
            $sameAs = collect([
                $profile->website,
                $profile->socialLinks->instagram,
                $profile->socialLinks->facebook,
                $profile->socialLinks->tiktok,
                $profile->socialLinks->youtube,
                $profile->socialLinks->linkedin,
            ])->filter()->values();

            if ($sameAs->isNotEmpty()) {
                $schema['sameAs'] = $sameAs->all();
            }
        }

        return $schema;
    }

    /** @return array<string, mixed> */
    public static function blogPosting(string $title, string $description, ?string $image, ?string $published, string $url, ?string $author = null): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $title,
            'description' => $description,
            'image' => $image,
            'datePublished' => $published,
            'author' => $author ? ['@type' => 'Person', 'name' => $author] : null,
            'mainEntityOfPage' => $url,
        ]);
    }

    /**
     * FAQPage schema.
     *
     * @param  array<int, array{q: string, a: string}>  $faqs
     * @return array<string, mixed>
     */
    public static function faq(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ])->all(),
        ];
    }
}
