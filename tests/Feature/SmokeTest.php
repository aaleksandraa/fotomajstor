<?php

namespace Tests\Feature;

use App\Enums\BlogStatus;
use App\Enums\UserRole;
use App\Http\Controllers\SitemapController;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\City;
use App\Models\Location;
use App\Models\PhotographerProfile;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioImage;
use App\Models\User;
use App\Support\SitemapCache;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected PhotographerProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LocationSeeder::class, CategorySeeder::class]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'password', 'role' => UserRole::Admin, 'email_verified_at' => now(),
        ]);

        $photographer = User::create([
            'name' => 'Test Fotograf', 'email' => 'foto@test.com',
            'password' => 'password', 'role' => UserRole::Photographer, 'email_verified_at' => now(),
        ]);

        // Observer auto-creates a profile; activate and enrich it.
        $this->profile = $photographer->photographerProfile;
        $this->profile->update(['active' => true, 'display_name' => 'Test Fotograf', 'slug' => 'test-fotograf']);
        $city = City::first();
        $category = Category::first();
        $this->profile->update(['primary_city_id' => $city->id, 'primary_country_id' => $city->country_id]);
        $this->profile->categories()->attach($category->id);
        $this->profile->cities()->attach($city->id);

        $album = PortfolioAlbum::create([
            'photographer_profile_id' => $this->profile->id, 'category_id' => $category->id,
            'title' => 'Galerija', 'slug' => 'galerija', 'active' => true,
        ]);
        PortfolioImage::create(['portfolio_album_id' => $album->id, 'image_path' => 'https://picsum.photos/seed/x/800/600', 'alt_text' => 'Test']);

        BlogPost::create([
            'title' => 'Test članak', 'slug' => 'test-clanak', 'content' => '<p>Sadržaj</p>',
            'status' => BlogStatus::Published, 'published_at' => now(),
        ]);
    }

    #[DataProvider('publicRoutes')]
    public function test_public_routes_return_ok(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public static function publicRoutes(): array
    {
        return [
            ['/'],
            ['/fotografi'],
            ['/kategorije'],
            ['/gradovi'],
            ['/politika-privatnosti'],
            ['/uslovi-koriscenja'],
            ['/blog'],
            ['/blog/test-clanak'],
            ['/fotografi/vjencanja'],
            ['/fotograf/test-fotograf'],
            ['/fotograf/test-fotograf/portfolio'],
            ['/fotograf/test-fotograf/portfolio/galerija'],
            ['/bih/fotografi'],
            ['/bih/fotografi/banja-luka'],
            ['/bih/fotografi-za/vjencanja'],
            ['/bih/fotografi/vjencanja/banja-luka'],
            ['/bih/fotograf/banja-luka'],
            ['/sitemap.xml'],
            ['/robots.txt'],
            ['/hr'],
            ['/sr/fotografi'],
            ['/en/kategorije'],
            ['/de/gradovi'],
            ['/it/fotograf/test-fotograf'],
        ];
    }

    public function test_fotomajstor_brand_is_consistent_in_public_seo_and_locales(): void
    {
        $this->assertSame('FotoMajstor', config('app.name'));
        $this->assertSame('FotoMajstor', config('mail.from.name'));

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>FotoMajstor | Fotografi i Videografi za Vjenčanja, Događaje i Snimanja</title>', false)
            ->assertSee('<meta name="description" content="Pronađite profesionalne fotografe i videografe u Bosni i Hercegovini, Srbiji, Hrvatskoj, Sloveniji i Crnoj Gori.', false)
            ->assertSee('<meta property="og:site_name" content="FotoMajstor">', false)
            ->assertSee('<meta property="og:image" content="'.asset('fotoMajstor.jpg').'">', false)
            ->assertSee('<meta property="og:image:type" content="image/jpeg">', false)
            ->assertSee('<meta property="og:image:width" content="1536">', false)
            ->assertSee('<meta property="og:image:height" content="1024">', false)
            ->assertSee('<meta name="twitter:image" content="'.asset('fotoMajstor.jpg').'">', false)
            ->assertSee('"@type":"Organization","name":"FotoMajstor"', false)
            ->assertSee('"image":"'.asset('fotoMajstor.jpg').'"', false)
            ->assertSee('"@type":"WebSite","name":"FotoMajstor"', false)
            ->assertDontSee('FotoMreža')
            ->assertDontSee('Pronađi Fotografa');

        $this->get('/en')
            ->assertOk()
            ->assertSee('<title>FotoMajstor | Photographers and Videographers for Weddings, Events and Productions</title>', false)
            ->assertSee('<meta property="og:site_name" content="FotoMajstor">', false)
            ->assertSee('<meta property="og:image" content="'.asset('fotoMajstor.jpg').'">', false)
            ->assertDontSee('Find a Photographer');
    }

    public function test_legacy_saved_seo_title_is_rendered_with_fotomajstor_brand(): void
    {
        $category = Category::firstOrFail();
        $category->update(['meta_title' => 'Fotografi za događaje | FotoMreža']);

        $this->get(route('category.show', $category->slug))
            ->assertOk()
            ->assertSee('<title>Fotografi za događaje | FotoMajstor</title>', false)
            ->assertDontSee('FotoMreža');
    }

    public function test_search_filters_by_category_and_date(): void
    {
        $this->get('/fotografi?category=svadbe')->assertOk();
        $this->get('/fotografi?date='.now()->toDateString())->assertOk();
    }

    public function test_portfolio_album_slug_is_scoped_to_photographer_and_not_duplicated_on_profile(): void
    {
        $category = Category::firstOrFail();
        $otherUser = User::create([
            'name' => 'Drugi Fotograf',
            'email' => 'drugi@test.com',
            'password' => 'password',
            'role' => UserRole::Photographer,
            'email_verified_at' => now(),
        ]);
        $otherProfile = $otherUser->photographerProfile;
        $otherProfile->update(['display_name' => 'Drugi Fotograf', 'slug' => 'drugi-fotograf']);

        $otherAlbum = PortfolioAlbum::create([
            'photographer_profile_id' => $otherProfile->id,
            'category_id' => $category->id,
            'title' => 'Galerija',
            'slug' => 'galerija',
            'active' => true,
        ]);
        PortfolioImage::create([
            'portfolio_album_id' => $otherAlbum->id,
            'image_path' => 'portfolio/drugi.webp',
            'alt_text' => 'Slika drugog fotografa',
        ]);

        $this->get('/fotograf/test-fotograf')
            ->assertOk()
            ->assertDontSee(route('photographer.portfolio.album', ['test-fotograf', 'galerija']));

        $this->get('/fotograf/test-fotograf/portfolio/galerija')
            ->assertOk()
            ->assertSee('Test')
            ->assertDontSee('Slika drugog fotografa');

        $this->get('/fotograf/drugi-fotograf/portfolio/galerija')
            ->assertOk()
            ->assertSee('Slika drugog fotografa');
    }

    public function test_profile_links_to_the_immersive_portfolio_with_its_images(): void
    {
        $this->get('/fotograf/test-fotograf')
            ->assertOk()
            ->assertSee(route('photographer.portfolio', 'test-fotograf'))
            ->assertSee('Otvori portfolio');

        $this->get('/fotograf/test-fotograf/portfolio')
            ->assertOk()
            ->assertSee('data-spherical-gallery', false)
            ->assertSee('rel="preload" as="image"', false)
            ->assertSee('https://picsum.photos/seed/x/800/600');
    }

    public function test_legacy_city_and_category_aliases_redirect_to_canonical_urls(): void
    {
        $this->get('/fotografi/grad/banja-luka')
            ->assertStatus(301)
            ->assertRedirect(route('landing.country.city', ['bih', 'banja-luka']));

        $this->get('/fotografi/svadbe')
            ->assertStatus(301)
            ->assertRedirect(route('category.show', 'vjencanja'));

        $this->get('/bih/fotografi/svadbe/banja-luka')
            ->assertStatus(301)
            ->assertRedirect(route('landing.category.city', ['bih', 'vjencanja', 'banja-luka']));
    }

    public function test_sitemap_includes_core_seo_landings_and_portfolio_albums(): void
    {
        Cache::forget(SitemapController::CACHE_KEY);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('sitemap.segment', ['bs', 'locations']))
            ->assertSee(route('sitemap.segment', ['sl', 'categories']))
            ->assertDontSee(route('sitemap.segment', ['en', 'profiles']));

        $this->get('/sitemaps/bs/locations.xml')
            ->assertOk()
            ->assertSee(route('landing.country', 'bih'))
            ->assertSee(route('landing.country.city', ['bih', 'banja-luka']))
            ->assertSee(route('landing.category.country', ['bih', 'vjencanja']))
            ->assertSee(route('landing.category.city', ['bih', 'vjencanja', 'banja-luka']))
            ->assertSee(route('landing.service.city', ['bih', 'fotograf', 'banja-luka']))
            ->assertDontSee('<priority>', false)
            ->assertDontSee('<changefreq>', false);

        $this->get('/sitemaps/bs/profiles.xml')
            ->assertOk()
            ->assertSee(route('photographer.show', 'test-fotograf'))
            ->assertSee(route('photographer.portfolio.album', ['test-fotograf', 'galerija']))
            ->assertHeader('Cache-Control', 'max-age=86400, public');
    }

    public function test_empty_location_is_noindex_and_excluded_from_location_sitemap(): void
    {
        $this->get('/bih/fotografi/sarajevo')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);

        $this->get('/sitemaps/bs/locations.xml')
            ->assertOk()
            ->assertDontSee(route('landing.country.city', ['bih', 'sarajevo']));
    }

    public function test_paginated_landing_has_self_canonical_and_visible_faq(): void
    {
        $url = route('landing.country.city', ['bih', 'banja-luka']);

        $this->get('/bih/fotografi/banja-luka?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$url.'?page=2">', false)
            ->assertSee('Kako pronaći fotografa u mjestu Banja Luka?')
            ->assertSee('FAQPage');
    }

    public function test_location_hierarchy_is_seeded_and_sitemap_cache_is_invalidated(): void
    {
        $city = City::where('slug', 'banja-luka')->firstOrFail();
        $this->assertDatabaseHas('locations', [
            'city_id' => $city->id,
            'type' => 'city',
            'slug' => 'banja-luka',
        ]);
        $this->assertGreaterThanOrEqual(5, Location::query()->distinct('country_id')->count('country_id'));

        Cache::put(SitemapCache::VERSION_KEY, 10);
        $city->update(['intro_text' => 'Novi lokalni SEO sadržaj.']);
        $this->assertGreaterThan(10, Cache::get(SitemapCache::VERSION_KEY));
    }

    public function test_hierarchy_place_uses_the_same_canonical_landing_routes(): void
    {
        $country = City::where('slug', 'banja-luka')->firstOrFail()->country;
        $place = Location::create([
            'country_id' => $country->id,
            'type' => 'place',
            'name' => 'Lokalno Mjesto',
            'slug' => 'lokalno-mjesto',
            'active' => true,
            'indexable' => true,
        ]);
        $this->profile->locations()->attach($place->id);

        $url = route('landing.country.city', [$country->slug, $place->slug]);
        $this->get(parse_url($url, PHP_URL_PATH))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$url.'">', false);

        SitemapCache::invalidate();
        $this->get('/sitemaps/bs/locations.xml')->assertSee($url);
    }

    public function test_untranslated_profile_locale_is_noindex_with_default_canonical(): void
    {
        $this->get('/en/fotograf/test-fotograf')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('photographer.show', 'test-fotograf').'">', false);
    }

    public function test_admin_panel_requires_admin_role(): void
    {
        $this->get('/admin')->assertRedirect();
        $this->actingAs($this->admin)->get('/admin')->assertOk();
        $this->actingAs($this->admin)->get('/admin/photographer-profiles')->assertOk();
        $this->actingAs($this->admin)->get('/admin/categories')->assertOk();
    }

    public function test_photographer_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->profile->user)->get('/admin')->assertForbidden();
    }

    public function test_photographer_can_access_dashboard(): void
    {
        $user = $this->profile->user;
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/dashboard/edit-profile')->assertOk();
        $this->actingAs($user)->get('/dashboard/availability')->assertOk();
        $this->actingAs($user)->get('/dashboard/portfolio-images')->assertOk();
        $this->actingAs($user)->get('/dashboard/portfolio-albums')->assertOk();
        $this->actingAs($user)->get('/dashboard/photographer-blog-posts')->assertOk();
    }

    public function test_admin_cannot_access_photographer_dashboard(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')->assertForbidden();
    }
}
