<?php

namespace App\Http\Controllers;

use App\Enums\ServiceType;
use App\Models\Category;
use App\Models\CategoryAlias;
use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use App\Models\PhotographerProfile;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;

class LocationLandingController extends Controller
{
    public function country(Country $country)
    {
        abort_unless($country->active, 404);

        $photographers = PhotographerProfile::search(['country' => $country->slug])
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->ranked()->paginate(12)->withQueryString();

        $topCities = City::active()->ordered()
            ->where('country_id', $country->id)
            ->whereHas('photographers', fn ($q) => $q->where('active', true))
            ->take(16)->get();

        $topCategories = Category::active()->ordered()
            ->whereHas('photographers', fn ($q) => $q->where('active', true)->where(function (Builder $query) use ($country) {
                $query->whereHas('countries', fn ($sub) => $sub->where('countries.id', $country->id))
                    ->orWhereHas('cities', fn ($sub) => $sub->where('cities.country_id', $country->id));
            }))
            ->take(12)->get();

        $seo = [
            'title' => app()->getLocale() === config('locales.default') && $country->meta_title ? seo_brand_title($country->meta_title) : seo_brand_title(__('Fotografi i videografi u :location | Pronađi Fotografa', ['location' => $country->name])),
            'description' => app()->getLocale() === config('locales.default') && $country->meta_description ? $country->meta_description : __('Pronađite profesionalne fotografe i videografe u :location. Pregledajte profile, portfolio, dostupnost i direktne kontakte.', ['location' => $country->name]),
            'canonical' => paginated_canonical(localized_route('landing.country', $country->slug)),
            'robots' => $photographers->total() > 0 ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => __('Početna'), 'url' => localized_route('home')],
                    ['name' => $country->name, 'url' => localized_route('landing.country', $country->slug)],
                ]),
            ],
        ];

        return view('public.listing', [
            'eyebrow' => __('Država'),
            'heading' => __('Fotografi u :name', ['name' => $country->name]),
            'intro' => app()->getLocale() === config('locales.default') && $country->intro_text ? $country->intro_text : __('Pregledajte fotografe i videografe dostupne u :name, po gradu, kategoriji i tipu usluge.', ['name' => $country->name]),
            'photographers' => $photographers,
            'relatedTitle' => __('Popularne pretrage'),
            'relatedLinks' => $topCities->map(fn ($city) => ['label' => __('Fotograf :name', ['name' => $city->name]), 'url' => localized_route('landing.country.city', [$country->slug, $city->slug])])
                ->merge($topCategories->map(fn ($category) => ['label' => __(':category u :location', ['category' => $category->name, 'location' => $country->name]), 'url' => localized_route('landing.category.country', [$country->slug, $category->slug])])),
            'seo' => $seo,
        ]);
    }

    public function countryCity(Country $country, string $city)
    {
        abort_unless($country->active, 404);
        $city = $this->resolveLocation($country, $city);

        $photographers = PhotographerProfile::search(['country' => $country->slug, ...$this->locationFilter($city)])
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->ranked()->paginate(12)->withQueryString();

        $topCategories = $this->categoriesForLocation($city)->take(12)->get();

        $faqs = [
            ['q' => __('Kako pronaći fotografa u mjestu :city?', ['city' => $city->name]), 'a' => __('Uporedite profile, portfolio, kategorije i dostupnost profesionalaca koji rade u mjestu :city.', ['city' => $city->name])],
            ['q' => __('Kako kontaktirati fotografa?'), 'a' => __('Otvorite profil i kontaktirajte profesionalca direktno telefonom, e-mailom ili putem društvenih mreža.')],
        ];

        $seo = [
            'title' => app()->getLocale() === config('locales.default') && $city->meta_title ? seo_brand_title($city->meta_title) : seo_brand_title(__('Fotografi u :city, :country | Pronađi Fotografa', ['city' => $city->name, 'country' => $country->name])),
            'description' => app()->getLocale() === config('locales.default') && $city->meta_description ? $city->meta_description : __('Pretražite fotografe i videografe u mjestu :city (:country). Pogledajte profile, portfolio i kontakt podatke.', ['city' => $city->name, 'country' => $country->name]),
            'canonical' => paginated_canonical(localized_route('landing.country.city', [$country->slug, $city->slug])),
            'robots' => $photographers->total() > 0 ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => __('Početna'), 'url' => localized_route('home')],
                    ['name' => $country->name, 'url' => localized_route('cities.index')],
                    ['name' => $city->name, 'url' => localized_route('landing.country.city', [$country->slug, $city->slug])],
                ]),
                Seo::faq($faqs),
            ],
        ];

        return view('public.listing', [
            'eyebrow' => $country->name,
            'heading' => __('Fotografi u :name', ['name' => $city->name]),
            'intro' => app()->getLocale() === config('locales.default') && $city->intro_text ? $city->intro_text : __('Profesionalni fotografi i videografi u mjestu :city, :country.', ['city' => $city->name, 'country' => $country->name]),
            'photographers' => $photographers,
            'relatedTitle' => __('Kategorije u ovom gradu'),
            'relatedLinks' => $topCategories->map(fn ($c) => ['label' => "{$c->name} — {$city->name}", 'url' => localized_route('landing.category.city', [$country->slug, $c->slug, $city->slug])]),
            'seoText' => $city->intro_text ?: __('FotoMreža povezuje vas sa fotografima i videografima koji rade u mjestu :city i okolnim lokacijama.', ['city' => $city->name]),
            'faqs' => $faqs,
            'seo' => $seo,
        ]);
    }

    public function categoryCountry(Country $country, string $category)
    {
        abort_unless($country->active, 404);
        $categoryModel = Category::active()->where('slug', $category)->first();
        if (! $categoryModel) {
            $alias = CategoryAlias::with('category')->where('slug', $category)->firstOrFail();

            return redirect(localized_route('landing.category.country', [$country->slug, $alias->category->slug]), 301);
        }
        $category = $categoryModel;

        $photographers = PhotographerProfile::search(['country' => $country->slug, 'category' => $category->slug])
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->ranked()->paginate(12)->withQueryString();

        $topCities = City::active()->ordered()
            ->where('country_id', $country->id)
            ->whereHas('photographers', fn ($q) => $q->where('active', true)->whereHas('categories', fn ($c) => $c->where('categories.id', $category->id)))
            ->take(16)->get();

        $singular = \Illuminate\Support\Str::lower($category->name);
        $seo = [
            'title' => seo_brand_title(__('Fotograf za :category u :location | Pronađi Fotografa', ['category' => $singular, 'location' => $country->name])),
            'description' => __('Pronađite fotografe i videografe za :category u :location. Pregledajte profile, portfolio, dostupnost i kontakt podatke.', ['category' => $singular, 'location' => $country->name]),
            'canonical' => paginated_canonical(localized_route('landing.category.country', [$country->slug, $category->slug])),
            'robots' => $photographers->total() > 0 ? 'index, follow' : 'noindex, follow',
            'image' => media_url($category->image),
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => __('Početna'), 'url' => localized_route('home')],
                    ['name' => $country->name, 'url' => localized_route('landing.country', $country->slug)],
                    ['name' => $category->name, 'url' => localized_route('landing.category.country', [$country->slug, $category->slug])],
                ]),
            ],
        ];

        return view('public.listing', [
            'eyebrow' => "{$category->name} · {$country->name}",
            'heading' => __('Fotograf za :category u :location', ['category' => $singular, 'location' => $country->name]),
            'intro' => __('Pronađite profesionalce za :category u :location. Uporedite portfolio, lokaciju i direktne kontakt podatke.', ['category' => $singular, 'location' => $country->name]),
            'photographers' => $photographers,
            'relatedTitle' => __('Gradovi za ovu kategoriju'),
            'relatedLinks' => $topCities->map(fn ($city) => ['label' => __(':category u :location', ['category' => $category->name, 'location' => $city->name]), 'url' => localized_route('landing.category.city', [$country->slug, $category->slug, $city->slug])]),
            'seo' => $seo,
        ]);
    }

    public function categoryCity(Country $country, string $category, string $city)
    {
        abort_unless($country->active, 404);
        $city = $this->resolveLocation($country, $city);
        $categoryModel = Category::active()->where('slug', $category)->first();
        if (! $categoryModel) {
            $alias = CategoryAlias::with('category')->where('slug', $category)->firstOrFail();

            return redirect(localized_route('landing.category.city', [$country->slug, $alias->category->slug, $city->slug]), 301);
        }
        $category = $categoryModel;

        $photographers = PhotographerProfile::search(['country' => $country->slug, 'category' => $category->slug, ...$this->locationFilter($city)])
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->ranked()->paginate(12)->withQueryString();

        $singular = \Illuminate\Support\Str::lower($category->name);
        $faqs = [
            ['q' => "Koliko ima fotografa za {$singular} u {$city->name}?", 'a' => "Trenutno prikazujemo {$photographers->total()} profesionalaca za {$singular} u mjestu {$city->name}."],
            ['q' => __('Kako kontaktirati fotografa?'), 'a' => __('Otvorite profil fotografa i kontaktirajte ga direktno putem telefona, e-maila ili društvenih mreža.')],
        ];

        $seo = [
            'title' => seo_brand_title(__('Fotograf za :category u :location | Pronađi Fotografa', ['category' => $singular, 'location' => $city->name])),
            'description' => __('Pronađite fotografe i videografe za :category u :location. Pregledajte profile, portfolio, dostupnost i kontakt podatke.', ['category' => $singular, 'location' => $city->name]),
            'image' => media_url($category->image),
            'canonical' => paginated_canonical(localized_route('landing.category.city', [$country->slug, $category->slug, $city->slug])),
            'robots' => $photographers->total() > 0 ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => __('Početna'), 'url' => localized_route('home')],
                    ['name' => $country->name, 'url' => localized_route('cities.index')],
                    ['name' => $city->name, 'url' => localized_route('landing.country.city', [$country->slug, $city->slug])],
                    ['name' => $category->name, 'url' => localized_route('landing.category.city', [$country->slug, $category->slug, $city->slug])],
                ]),
                Seo::faq($faqs),
            ],
        ];

        return view('public.listing', [
            'eyebrow' => "{$category->name} · {$country->name}",
            'heading' => __('Fotograf za :category u :location', ['category' => $singular, 'location' => $city->name]),
            'intro' => __('Pronađite najbolje fotografe i videografe za :category u mjestu :location. Pregledajte portfolio i kontaktirajte direktno.', ['category' => $singular, 'location' => $city->name]),
            'photographers' => $photographers,
            'relatedTitle' => null,
            'relatedLinks' => collect(),
            'seoText' => __('Pregledajte portfolio radove, iskustvo i dostupnost profesionalaca za :category u mjestu :city. Sinonimi poput fotografisanja, fotografiranja i fotkanja obuhvaćeni su ovom jedinstvenom ponudom.', ['category' => $singular, 'city' => $city->name]),
            'faqs' => $faqs,
            'seo' => $seo,
        ]);
    }

    public function serviceCity(Country $country, string $service, string $city)
    {
        abort_unless($country->active, 404);

        $serviceType = ServiceType::fromSeoSlug($service);
        abort_unless($serviceType, 404);

        $city = $this->resolveLocation($country, $city);

        $photographers = PhotographerProfile::search(['country' => $country->slug, ...$this->locationFilter($city)])
            ->whereIn('service_type', $serviceType->matchingValues())
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->ranked()->paginate(12)->withQueryString();

        $topCategories = $this->categoriesForLocation($city, $serviceType->matchingValues())->take(12)->get();

        $serviceTitle = $serviceType->searchTitle();
        $seo = [
            'title' => seo_brand_title(__(':service u :location | Pronađi Fotografa', ['service' => $serviceTitle, 'location' => $city->name])),
            'description' => __('Pronađite uslugu :service u mjestu :city, :country. Pogledajte portfolio, dostupnost i direktne kontakt podatke.', ['service' => $serviceTitle, 'city' => $city->name, 'country' => $country->name]),
            'canonical' => paginated_canonical(localized_route('landing.service.city', [$country->slug, $serviceType->seoSlug(), $city->slug])),
            'robots' => $photographers->total() > 0 ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => __('Početna'), 'url' => localized_route('home')],
                    ['name' => $country->name, 'url' => localized_route('landing.country', $country->slug)],
                    ['name' => $city->name, 'url' => localized_route('landing.country.city', [$country->slug, $city->slug])],
                    ['name' => $serviceTitle, 'url' => localized_route('landing.service.city', [$country->slug, $serviceType->seoSlug(), $city->slug])],
                ]),
            ],
        ];

        return view('public.listing', [
            'eyebrow' => "{$country->name} · {$city->name}",
            'heading' => __(':service u :location', ['service' => $serviceTitle, 'location' => $city->name]),
            'intro' => __('Pregledajte profile za :service u mjestu :location. Portfolio, iskustvo i kontakt su dostupni direktno na profilu.', ['service' => $serviceTitle, 'location' => $city->name]),
            'photographers' => $photographers,
            'relatedTitle' => __('Popularne kategorije za ovu uslugu'),
            'relatedLinks' => $topCategories->map(fn ($category) => ['label' => __(':category u :location', ['category' => $category->name, 'location' => $city->name]), 'url' => localized_route('landing.category.city', [$country->slug, $category->slug, $city->slug])]),
            'seo' => $seo,
        ]);
    }

    private function resolveLocation(Country $country, string $slug): City|Location
    {
        return City::active()->where('country_id', $country->id)->where('slug', $slug)->first()
            ?? Location::active()->where('country_id', $country->id)->whereNull('city_id')->where('slug', $slug)->firstOrFail();
    }

    /** @return array<string, int|string> */
    private function locationFilter(City|Location $location): array
    {
        return $location instanceof City
            ? ['city' => $location->slug]
            : ['location' => $location->id];
    }

    private function categoriesForLocation(City|Location $location, ?array $serviceTypes = null): Builder
    {
        return Category::active()->ordered()->whereHas('photographers', function (Builder $query) use ($location, $serviceTypes) {
            $query->where('active', true);
            if ($serviceTypes) {
                $query->whereIn('service_type', $serviceTypes);
            }
            $location instanceof City
                ? $query->whereHas('cities', fn ($sub) => $sub->where('cities.id', $location->id))
                : $query->whereHas('locations', fn ($sub) => $sub->where('locations.id', $location->id));
        });
    }
}
