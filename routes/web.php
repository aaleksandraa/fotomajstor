<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationLandingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Support\LocalizedUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemaps/{locale}/{section}.xml', [SitemapController::class, 'segment'])->name('sitemap.segment');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

Route::get('/jezik/{locale}', function (Request $request, string $locale) {
    if (! array_key_exists($locale, config('locales.supported', []))) {
        return redirect()->back();
    }

    $request->session()->put('locale', $locale);
    $target = $request->query('redirect', url('/'));
    $targetHost = parse_url($target, PHP_URL_HOST);
    $targetPath = '/'.ltrim((string) parse_url($target, PHP_URL_PATH), '/');

    if ($targetHost && $targetHost !== $request->getHost()) {
        $target = url('/');
        $targetPath = '/';
    }

    if (str_starts_with($targetPath, '/dashboard') || str_starts_with($targetPath, '/admin')) {
        return redirect($target);
    }

    return redirect(LocalizedUrl::for($target, $locale));
})->name('locale.switch');

$publicRoutes = function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/kategorije', [PageController::class, 'categories'])->name('categories.index');
    Route::get('/gradovi', [PageController::class, 'cities'])->name('cities.index');

    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

    Route::get('/fotografi', [SearchController::class, 'index'])->name('search');
    Route::get('/fotografi/grad/{city:slug}', [CityController::class, 'redirectToCanonical'])->name('city.show');
    Route::get('/fotografi/{category}', [CategoryController::class, 'show'])->name('category.show');

    Route::get('/fotograf/{photographer:slug}/blog/{post}', [PhotographerController::class, 'blogPost'])->name('photographer.blog.show');
    Route::get('/fotograf/{photographer:slug}/portfolio/{album:slug}', [PhotographerController::class, 'portfolioAlbum'])
        ->scopeBindings()
        ->name('photographer.portfolio.album');
    Route::get('/fotograf/{photographer:slug}', [PhotographerController::class, 'show'])->name('photographer.show');

    Route::get('/{country:slug}/fotografi/{category}/{city}', [LocationLandingController::class, 'categoryCity'])->name('landing.category.city');
    Route::get('/{country:slug}/fotografi-za/{category}', [LocationLandingController::class, 'categoryCountry'])->name('landing.category.country');
    Route::get('/{country:slug}/fotografi', [LocationLandingController::class, 'country'])->name('landing.country');
    Route::get('/{country:slug}/{service}/{city}', [LocationLandingController::class, 'serviceCity'])
        ->whereIn('service', ['fotograf', 'videograf', 'fotograf-videograf'])
        ->name('landing.service.city');
    Route::get('/{country:slug}/fotografi/{city}', [LocationLandingController::class, 'countryCity'])->name('landing.country.city');
};

foreach (array_keys(config('locales.supported', [])) as $locale) {
    if ($locale === config('locales.default', 'bs')) {
        continue;
    }

    Route::prefix($locale)->name($locale.'.')->group($publicRoutes);
}

$publicRoutes();
