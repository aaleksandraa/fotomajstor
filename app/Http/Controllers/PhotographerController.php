<?php

namespace App\Http\Controllers;

use App\Models\PhotographerProfile;
use App\Models\PortfolioAlbum;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PhotographerController extends Controller
{
    public function show(Request $request, PhotographerProfile $photographer)
    {
        $isOwnerPreview = ! $photographer->active && $request->user()?->is($photographer->user);

        abort_unless($photographer->active || $isOwnerPreview, 404);

        $photographer->load([
            'socialLinks', 'categories', 'cities.country', 'primaryCity', 'primaryCountry',
            'albums' => fn ($q) => $q->active()->with(['images', 'videos']),
            'blogPosts' => fn ($q) => $q->published()->latest('published_at'),
        ]);

        if (! $isOwnerPreview) {
            $this->recordView($request, $photographer);
        }

        $start = Carbon::today();
        $end = $start->copy()->addWeeks(4);
        $unavailable = $photographer->unavailableDates()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')->map(fn ($d) => Carbon::parse($d)->toDateString())->all();

        $calendar = [];
        for ($d = $start->copy(); $d->lt($end); $d->addDay()) {
            $calendar[] = ['date' => $d->copy(), 'available' => ! in_array($d->toDateString(), $unavailable, true)];
        }

        $cityName = $photographer->primaryCity?->name;
        $seo = [
            'title' => "{$photographer->display_name} - {$photographer->service_type->label()}".($cityName ? " | {$cityName}" : ''),
            'description' => "Pogledajte profil, portfolio, dostupnost i kontakt podatke za {$photographer->display_name}. Kategorije: "
                .$photographer->categories->pluck('name')->take(4)->implode(', ').'.',
            'canonical' => localized_route('photographer.show', $photographer->slug),
            'canonicalLocale' => config('locales.default'),
            'robots' => ! $isOwnerPreview && app()->getLocale() === config('locales.default') ? 'index, follow' : 'noindex, follow',
            'image' => media_url($photographer->cover_image ?? $photographer->profile_image),
            'type' => 'profile',
            'locales' => [config('locales.default')],
            'jsonLd' => [
                Seo::professionalService($photographer),
                Seo::breadcrumbs([
                    ['name' => 'Početna', 'url' => localized_route('home')],
                    ['name' => 'Fotografi', 'url' => localized_route('search')],
                    ['name' => $photographer->display_name, 'url' => localized_route('photographer.show', $photographer->slug)],
                ]),
            ],
        ];

        return view('public.photographer', compact('photographer', 'calendar', 'seo', 'isOwnerPreview'));
    }

    public function blogPost(PhotographerProfile $photographer, string $post)
    {
        abort_unless($photographer->active, 404);

        $article = $photographer->blogPosts()->published()->where('slug', $post)->with('images', 'category', 'city')->firstOrFail();
        $description = safe_public_text($article->meta_description ?? $article->excerpt ?? $article->content);

        $seo = [
            'title' => "{$article->title} - {$photographer->display_name}",
            'description' => $description,
            'image' => media_url($article->featured_image),
            'type' => 'article',
            'locales' => [config('locales.default')],
            'canonical' => route('photographer.blog.show', [$photographer->slug, $article->slug]),
            'canonicalLocale' => config('locales.default'),
            'robots' => app()->getLocale() === config('locales.default') ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [
                Seo::blogPosting(
                    $article->title,
                    $description,
                    media_url($article->featured_image),
                    $article->published_at?->toIso8601String(),
                    localized_route('photographer.blog.show', [$photographer->slug, $article->slug]),
                    $photographer->display_name,
                ),
            ],
        ];

        return view('public.photographer-blog', compact('photographer', 'article', 'seo'));
    }

    public function portfolioAlbum(PhotographerProfile $photographer, PortfolioAlbum $album)
    {
        abort_unless($photographer->active, 404);
        abort_unless($album->active && $album->photographer_profile_id === $photographer->id, 404);

        $photographer->load(['primaryCity', 'primaryCountry', 'categories']);
        $album->load(['category', 'images', 'videos']);

        $cityName = $photographer->primaryCity?->name;
        $categoryName = $album->category?->name;
        $descriptionParts = array_filter([
            $categoryName ? "Portfolio za {$categoryName}" : 'Portfolio album',
            $cityName,
            $photographer->display_name,
        ]);

        $seo = [
            'title' => "{$album->title} - {$photographer->display_name}",
            'description' => implode(' | ', $descriptionParts).'. Pogledajte odabrane fotografije i video radove iz ovog albuma.',
            'canonical' => localized_route('photographer.portfolio.album', [$photographer->slug, $album->slug]),
            'canonicalLocale' => config('locales.default'),
            'robots' => app()->getLocale() === config('locales.default') ? 'index, follow' : 'noindex, follow',
            'image' => media_url($album->cover_image ?? $album->images->first()?->image_path ?? $photographer->cover_image ?? $photographer->profile_image),
            'type' => 'article',
            'locales' => [config('locales.default')],
            'jsonLd' => [
                Seo::breadcrumbs([
                    ['name' => 'Početna', 'url' => localized_route('home')],
                    ['name' => 'Fotografi', 'url' => localized_route('search')],
                    ['name' => $photographer->display_name, 'url' => localized_route('photographer.show', $photographer->slug)],
                    ['name' => $album->title, 'url' => localized_route('photographer.portfolio.album', [$photographer->slug, $album->slug])],
                ]),
            ],
        ];

        return view('public.portfolio-album', compact('photographer', 'album', 'seo'));
    }

    protected function recordView(Request $request, PhotographerProfile $photographer): void
    {
        $key = 'viewed_profile_'.$photographer->id;
        if ($request->session()->has($key)) {
            return;
        }

        $request->session()->put($key, true);
        $photographer->increment('profile_views');
        $photographer->views()->create([
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'viewed_at' => now(),
        ]);
    }
}
