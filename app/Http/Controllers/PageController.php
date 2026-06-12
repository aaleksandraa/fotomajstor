<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Support\Seo;

class PageController extends Controller
{
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
