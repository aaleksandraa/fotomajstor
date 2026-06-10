<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\City;
use App\Models\PhotographerProfile;
use App\Models\PortfolioImage;
use App\Support\Seo;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'photographers' => PhotographerProfile::active()->count(),
            'cities' => City::active()->count(),
            'categories' => Category::active()->count(),
            'photos' => PortfolioImage::count(),
        ];

        $popularCategories = Category::active()->ordered()
            ->withCount(['photographers as photographers_count' => fn ($q) => $q->where('active', true)])
            ->take(8)->get();

        $popularCities = City::active()->ordered()->with('country')
            ->withCount(['photographers as photographers_count' => fn ($q) => $q->where('active', true)])
            ->orderByDesc('photographers_count')
            ->take(8)->get();

        $featured = PhotographerProfile::active()->ranked()
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())])
            ->take(6)->get();

        $latestWork = PortfolioImage::query()
            ->whereHas('album', fn ($q) => $q->active()->whereHas('photographerProfile', fn ($p) => $p->where('active', true)))
            ->with('album.photographerProfile')
            ->latest('id')->take(10)->get();

        $blogPosts = BlogPost::published()->with('category')->latest('published_at')->take(3)->get();

        $popularSearches = [
            ['label' => 'Fotograf za svadbe', 'url' => localized_route('category.show', 'svadbe')],
            ['label' => 'Fotograf Banja Luka', 'url' => localized_route('landing.service.city', ['bih', 'fotograf', 'banja-luka'])],
            ['label' => 'Vjenčanja Sarajevo', 'url' => localized_route('landing.category.city', ['bih', 'vjencanja', 'sarajevo'])],
            ['label' => 'Dron snimanje', 'url' => localized_route('category.show', 'dron-video')],
            ['label' => 'Portreti Beograd', 'url' => localized_route('landing.category.city', ['srbija', 'portreti', 'beograd'])],
        ];

        $faqs = [
            ['q' => 'Kako pronaći fotografa u mom gradu?', 'a' => 'Koristite pretragu i odaberite svoj grad, kategoriju i datum. Prikazaćemo vam dostupne profesionalce sa portfolijom i kontakt podacima.'],
            ['q' => 'Da li plaćam kroz platformu?', 'a' => 'Ne. Platforma je katalog — fotografa kontaktirate direktno putem telefona, e-maila ili društvenih mreža, bez provizije.'],
            ['q' => 'Kako da znam da li je fotograf slobodan?', 'a' => 'Svaki profil ima kalendar dostupnosti. Pri pretrazi po datumu prikazujemo samo fotografe koji su tog dana slobodni.'],
            ['q' => 'Mogu li i ja kao fotograf kreirati profil?', 'a' => 'Da, registrujte se kao fotograf, kreirajte profil, dodajte portfolio i lokacije rada. Nakon odobrenja profil postaje javan.'],
        ];

        $seo = [
            'title' => 'FotoMreža — fotografi i videografi za vaš događaj',
            'description' => 'FotoMreža je regionalni katalog fotografa i videografa za BiH, Srbiju, Hrvatsku, Sloveniju i Crnu Goru. Pretražite po gradu, kategoriji i datumu.',
            'jsonLd' => [Seo::organization(), Seo::website(), Seo::faq($faqs)],
        ];

        return view('public.home', compact(
            'stats', 'popularCategories', 'popularCities', 'featured', 'latestWork', 'blogPosts', 'popularSearches', 'faqs', 'seo'
        ));
    }
}
