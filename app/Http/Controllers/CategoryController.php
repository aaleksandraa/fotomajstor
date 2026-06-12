<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryAlias;
use App\Models\City;
use App\Models\PhotographerProfile;
use App\Support\Seo;

class CategoryController extends Controller
{
    public function show(string $category)
    {
        $categoryModel = Category::active()->where('slug', $category)->first();

        if (! $categoryModel) {
            $alias = CategoryAlias::with('category')->where('slug', $category)->firstOrFail();

            return redirect(localized_route('category.show', $alias->category->slug), 301);
        }

        $category = $categoryModel;
        abort_unless($category->active, 404);

        $photographers = PhotographerProfile::search(['category' => $category->slug])
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->ranked()->paginate(12)->withQueryString();

        $topCities = City::active()->ordered()->with('country')
            ->whereHas('photographers', fn ($q) => $q->where('active', true)->whereHas('categories', fn ($c) => $c->where('categories.id', $category->id)))
            ->take(12)->get();

        $seo = [
            'title' => app()->getLocale() === config('locales.default') && $category->meta_title
                ? seo_brand_title($category->meta_title)
                : seo_brand_title(__('Fotografi za :name | FotoMajstor', ['name' => $category->name])),
            'description' => app()->getLocale() === config('locales.default') && $category->meta_description
                ? $category->meta_description
                : __('Pronađite fotografe i videografe za :name. Pogledajte portfolio, dostupnost i kontakt podatke.', ['name' => $category->name]),
            'canonical' => paginated_canonical(localized_route('category.show', $category->slug)),
            'image' => media_url($category->image),
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => __('Početna'), 'url' => localized_route('home')],
                    ['name' => __('Kategorije'), 'url' => localized_route('categories.index')],
                    ['name' => $category->name, 'url' => localized_route('category.show', $category->slug)],
                ]),
            ],
        ];

        return view('public.listing', [
            'eyebrow' => __('Kategorija'),
            'heading' => __('Fotografi za :name', ['name' => $category->name]),
            'intro' => app()->getLocale() === config('locales.default') && $category->intro_text
                ? $category->intro_text
                : __('Pregledajte provjerene fotografe i videografe specijalizovane za kategoriju :name.', ['name' => $category->name]),
            'photographers' => $photographers,
            'relatedTitle' => __('Popularni gradovi'),
            'relatedLinks' => $topCities->map(fn ($c) => ['label' => "{$category->name} — {$c->name}", 'url' => localized_route('landing.category.city', [$c->country->slug, $category->slug, $c->slug])]),
            'seo' => $seo,
        ]);
    }
}
