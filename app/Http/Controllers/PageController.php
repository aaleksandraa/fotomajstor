<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Support\Seo;

class PageController extends Controller
{
    public function privacy()
    {
        $seo = [
            'title' => __('Politika privatnosti | FotoMajstor'),
            'description' => __('Saznajte kako FotoMajstor prikuplja, koristi, čuva i štiti lične podatke korisnika i posjetilaca.'),
            'canonical' => localized_route('privacy'),
            'canonicalLocale' => config('locales.default'),
            'locales' => [config('locales.default')],
            'robots' => app()->getLocale() === config('locales.default') ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [Seo::breadcrumbs([
                ['name' => __('Početna'), 'url' => localized_route('home')],
                ['name' => __('Politika privatnosti'), 'url' => localized_route('privacy')],
            ])],
        ];

        return view('public.legal', [
            'title' => __('Politika privatnosti'),
            'intro' => __('Ova politika objašnjava kako FotoMajstor obrađuje lične podatke posjetilaca i registrovanih fotografa i videografa.'),
            'partial' => 'public.partials.privacy-content',
            'seo' => $seo,
        ]);
    }

    public function terms()
    {
        $seo = [
            'title' => __('Uslovi korištenja | FotoMajstor'),
            'description' => __('Pročitajte pravila korištenja platforme FotoMajstor za posjetioce, fotografe i videografe.'),
            'canonical' => localized_route('terms'),
            'canonicalLocale' => config('locales.default'),
            'locales' => [config('locales.default')],
            'robots' => app()->getLocale() === config('locales.default') ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [Seo::breadcrumbs([
                ['name' => __('Početna'), 'url' => localized_route('home')],
                ['name' => __('Uslovi korištenja'), 'url' => localized_route('terms')],
            ])],
        ];

        return view('public.legal', [
            'title' => __('Uslovi korištenja'),
            'intro' => __('Ovi uslovi uređuju korištenje platforme FotoMajstor i odnos između platforme, posjetilaca i registrovanih profesionalaca.'),
            'partial' => 'public.partials.terms-content',
            'seo' => $seo,
        ]);
    }

    public function categories()
    {
        $categories = Category::active()->ordered()
            ->withCount(['photographers as photographers_count' => fn ($q) => $q->where('active', true)])
            ->get();

        $seo = [
            'title' => 'Kategorije fotografisanja i snimanja | FotoMajstor',
            'description' => 'Pregledajte sve kategorije — od vjenčanja i portreta do dron snimanja i komercijalne fotografije. Pronađite specijalistu za vaš projekat.',
            'jsonLd' => [Seo::breadcrumbs([
                ['name' => __('Početna'), 'url' => localized_route('home')],
                ['name' => __('Kategorije'), 'url' => localized_route('categories.index')],
            ])],
        ];

        return view('public.categories', compact('categories', 'seo'));
    }

    public function cities()
    {
        $countries = Country::active()->ordered()
            ->with(['cities' => fn ($q) => $q->active()->ordered()->withCount(['photographers as photographers_count' => fn ($p) => $p->where('active', true)])])
            ->get();

        $seo = [
            'title' => 'Gradovi i mjesta | FotoMajstor',
            'description' => 'Pronađite fotografe i videografe po gradovima u Bosni i Hercegovini, Srbiji, Hrvatskoj, Sloveniji i Crnoj Gori.',
            'jsonLd' => [Seo::breadcrumbs([
                ['name' => __('Početna'), 'url' => localized_route('home')],
                ['name' => __('Gradovi'), 'url' => localized_route('cities.index')],
            ])],
        ];

        return view('public.cities', compact('countries', 'seo'));
    }
}
