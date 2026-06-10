<?php

namespace App\Http\Controllers;

use App\Enums\ServiceType;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use App\Models\PhotographerBlogPost;
use App\Models\PhotographerProfile;
use App\Models\PortfolioAlbum;
use App\Support\LocalizedUrl;
use App\Support\SitemapCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public const CACHE_KEY = SitemapCache::VERSION_KEY;

    public const SECTIONS = ['core', 'locations', 'categories', 'profiles', 'content'];

    public function index()
    {
        $default = config('locales.default');
        $sitemaps = collect(array_keys(config('locales.supported', [])))
            ->flatMap(fn ($locale) => collect(self::SECTIONS)
                ->reject(fn ($section) => $locale !== $default && in_array($section, ['profiles', 'content'], true))
                ->map(fn ($section) => ['loc' => route('sitemap.segment', [$locale, $section])]));

        return response()
            ->view('sitemap-index', ['sitemaps' => $sitemaps])
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function segment(string $locale, string $section)
    {
        abort_unless(array_key_exists($locale, config('locales.supported', [])), 404);
        abort_unless(in_array($section, self::SECTIONS, true), 404);

        $key = SitemapCache::key($locale, $section);
        $urls = Cache::remember($key, now()->addDay(), fn () => $this->buildUrls($locale, $section));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    private function buildUrls(string $locale, string $section): Collection
    {
        $urls = collect();

        match ($section) {
            'core' => $this->addCorePages($urls, $locale),
            'locations' => $this->addLocationPages($urls, $locale),
            'categories' => $this->addCategoryPages($urls, $locale),
            'profiles' => $this->addProfilePages($urls, $locale),
            'content' => $this->addContentPages($urls, $locale),
        };

        return $urls->unique('loc')->sortBy('loc')->values();
    }

    private function add(Collection $urls, string $loc, string $locale, ?string $lastmod = null, ?array $alternateLocales = null): void
    {
        $urls->push(array_filter([
            'loc' => LocalizedUrl::for($loc, $locale),
            'lastmod' => $lastmod,
            'alternates' => LocalizedUrl::alternates($loc, $alternateLocales),
        ]));
    }

    private function addCorePages(Collection $urls, string $locale): void
    {
        foreach (['home', 'search', 'categories.index', 'cities.index', 'blog.index'] as $routeName) {
            $this->add($urls, route($routeName), $locale);
        }
    }

    private function addCategoryPages(Collection $urls, string $locale): void
    {
        Category::active()
            ->whereHas('photographers', fn ($query) => $query->where('active', true))
            ->get()
            ->each(fn ($category) => $this->add($urls, route('category.show', $category->slug), $locale, $category->updated_at?->toAtomString()));
    }

    private function addLocationPages(Collection $urls, string $locale): void
    {
        Country::active()
            ->where(function ($query) {
                $query->whereHas('photographers', fn ($sub) => $sub->where('active', true))
                    ->orWhereHas('cities.photographers', fn ($sub) => $sub->where('active', true));
            })->get()
            ->each(fn ($country) => $this->add($urls, route('landing.country', $country->slug), $locale, $country->updated_at?->toAtomString()));

        City::active()->with('country')
            ->whereHas('photographers', fn ($query) => $query->where('active', true))
            ->get()->each(fn ($city) => $this->add($urls, route('landing.country.city', [$city->country->slug, $city->slug]), $locale, $city->updated_at?->toAtomString()));

        $this->addCategoryCountryPages($urls, $locale);
        $this->addCategoryCityPages($urls, $locale);
        $this->addServiceCityPages($urls, $locale);
        $this->addHierarchyLocationPages($urls, $locale);
    }

    private function addCategoryCountryPages(Collection $urls, string $locale): void
    {
        DB::table('photographer_category as pc')
            ->join('photographer_profiles as p', 'p.id', '=', 'pc.photographer_profile_id')
            ->join('categories as cat', 'cat.id', '=', 'pc.category_id')
            ->join('photographer_city as pcity', 'p.id', '=', 'pcity.photographer_profile_id')
            ->join('cities as city', 'city.id', '=', 'pcity.city_id')
            ->join('countries as co', 'co.id', '=', 'city.country_id')
            ->where('p.active', true)->where('cat.active', true)->where('city.active', true)->where('co.active', true)
            ->select('cat.slug as category', 'co.slug as country')->distinct()->get()
            ->each(fn ($combo) => $this->add($urls, route('landing.category.country', [$combo->country, $combo->category]), $locale));
    }

    private function addCategoryCityPages(Collection $urls, string $locale): void
    {
        DB::table('photographer_category as pc')
            ->join('photographer_city as pcity', 'pc.photographer_profile_id', '=', 'pcity.photographer_profile_id')
            ->join('photographer_profiles as p', 'p.id', '=', 'pc.photographer_profile_id')
            ->join('categories as cat', 'cat.id', '=', 'pc.category_id')
            ->join('cities as city', 'city.id', '=', 'pcity.city_id')
            ->join('countries as co', 'co.id', '=', 'city.country_id')
            ->where('p.active', true)->where('cat.active', true)->where('city.active', true)->where('co.active', true)
            ->select('cat.slug as category', 'city.slug as city', 'co.slug as country')->distinct()->get()
            ->each(fn ($combo) => $this->add($urls, route('landing.category.city', [$combo->country, $combo->category, $combo->city]), $locale));
    }

    private function addServiceCityPages(Collection $urls, string $locale): void
    {
        DB::table('photographer_profiles as p')
            ->join('photographer_city as pcity', 'p.id', '=', 'pcity.photographer_profile_id')
            ->join('cities as city', 'city.id', '=', 'pcity.city_id')
            ->join('countries as co', 'co.id', '=', 'city.country_id')
            ->where('p.active', true)->where('city.active', true)->where('co.active', true)
            ->select('p.service_type', 'city.slug as city', 'co.slug as country')->distinct()->get()
            ->each(function ($combo) use ($urls, $locale) {
                $service = ServiceType::tryFrom($combo->service_type);
                if (! $service) {
                    return;
                }
                $slugs = $service === ServiceType::PhotographerVideographer
                    ? ['fotograf', 'videograf', 'fotograf-videograf']
                    : [$service->seoSlug()];
                foreach ($slugs as $slug) {
                    $this->add($urls, route('landing.service.city', [$combo->country, $slug, $combo->city]), $locale);
                }
            });
    }

    private function addHierarchyLocationPages(Collection $urls, string $locale): void
    {
        Location::active()
            ->whereNull('city_id')
            ->whereHas('photographers', fn ($query) => $query->where('active', true))
            ->with(['country', 'photographers' => fn ($query) => $query->where('active', true)->with('categories')])
            ->get()
            ->each(function (Location $location) use ($urls, $locale) {
                $base = [$location->country->slug, $location->slug];
                $this->add($urls, route('landing.country.city', $base), $locale, $location->updated_at?->toAtomString());

                $location->photographers->flatMap->categories->where('active', true)->unique('id')->each(
                    fn ($category) => $this->add($urls, route('landing.category.city', [$location->country->slug, $category->slug, $location->slug]), $locale)
                );

                $location->photographers->pluck('service_type')->filter()->unique()->each(function ($serviceType) use ($urls, $locale, $location) {
                    $serviceType = $serviceType instanceof ServiceType ? $serviceType : ServiceType::tryFrom((string) $serviceType);
                    if ($serviceType) {
                        $this->add($urls, route('landing.service.city', [$location->country->slug, $serviceType->seoSlug(), $location->slug]), $locale);
                    }
                });
            });
    }

    private function addProfilePages(Collection $urls, string $locale): void
    {
        if ($locale !== config('locales.default')) {
            return;
        }

        PhotographerProfile::active()->get()->each(
            fn ($profile) => $this->add($urls, route('photographer.show', $profile->slug), $locale, $profile->updated_at?->toAtomString(), [config('locales.default')])
        );
        PortfolioAlbum::active()->whereHas('photographerProfile', fn ($query) => $query->where('active', true))
            ->with('photographerProfile:id,slug,active')->get()->each(
                fn ($album) => $this->add($urls, route('photographer.portfolio.album', [$album->photographerProfile->slug, $album->slug]), $locale, $album->updated_at?->toAtomString(), [config('locales.default')])
            );
    }

    private function addContentPages(Collection $urls, string $locale): void
    {
        if ($locale !== config('locales.default')) {
            return;
        }

        BlogPost::published()->get()->each(
            fn ($post) => $this->add($urls, route('blog.show', $post->slug), $locale, $post->updated_at?->toAtomString(), [config('locales.default')])
        );
        PhotographerBlogPost::published()->with('photographerProfile')->get()->each(function ($post) use ($urls, $locale) {
            if ($post->photographerProfile?->active) {
                $this->add($urls, route('photographer.blog.show', [$post->photographerProfile->slug, $post->slug]), $locale, $post->updated_at?->toAtomString(), [config('locales.default')]);
            }
        });
    }
}
