<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\PhotographerProfile;
use App\Support\Seo;

class CityController extends Controller
{
    public function redirectToCanonical(City $city)
    {
        $city->load('country');
        abort_unless($city->active && $city->country?->active, 404);

        return redirect(localized_route('landing.country.city', [$city->country->slug, $city->slug]), 301);
    }

    public function show(City $city)
    {
        abort_unless($city->active, 404);
        $city->load('country');

        $photographers = PhotographerProfile::search(['city' => $city->slug])
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->ranked()->paginate(12)->withQueryString();

        $topCategories = Category::active()->ordered()
            ->whereHas('photographers', fn ($q) => $q->where('active', true)->whereHas('cities', fn ($c) => $c->where('cities.id', $city->id)))
            ->take(12)->get();

        $seo = [
            'title' => __('Fotografi u :name | FotoMajstor', ['name' => $city->name]),
            'description' => __('Pretražite fotografe i videografe u mjestu :name. Pogledajte profile, portfolio i kontakt podatke.', ['name' => $city->name]),
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => __('Početna'), 'url' => localized_route('home')],
                    ['name' => __('Gradovi'), 'url' => localized_route('cities.index')],
                    ['name' => $city->name, 'url' => localized_route('city.show', $city->slug)],
                ]),
            ],
        ];

        return view('public.listing', [
            'eyebrow' => $city->country?->name ?? __('Grad'),
            'heading' => __('Fotografi u :name', ['name' => $city->name]),
            'intro' => __('Pronađite provjerene fotografe i videografe u mjestu :name.', ['name' => $city->name]),
            'photographers' => $photographers,
            'relatedTitle' => __('Kategorije u ovom gradu'),
            'relatedLinks' => $topCategories->map(fn ($c) => ['label' => "{$c->name} — {$city->name}", 'url' => localized_route('search', ['category' => $c->slug, 'city' => $city->slug])]),
            'seo' => $seo,
        ]);
    }
}
