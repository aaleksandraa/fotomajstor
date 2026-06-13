<?php

namespace Tests\Feature;

use App\Enums\BlogStatus;
use App\Enums\PhotographerBlogStatus;
use App\Enums\UserRole;
use App\Filament\Dashboard\Resources\PhotographerBlogPostResource\Pages\CreatePhotographerBlogPost as CreatePhotographerBlogPostPage;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\User;
use App\Services\PortfolioService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecuritySeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_blog_content_is_sanitized(): void
    {
        BlogPost::create([
            'title' => 'Siguran clanak',
            'slug' => 'siguran-clanak',
            'content' => '<p>Siguran tekst</p><script>alert(1)</script><img src="x" onerror="alert(2)"><a href="javascript:alert(3)">Los link</a><a href="https://example.com" target="_blank">Dobar link</a>',
            'status' => BlogStatus::Published,
            'published_at' => now(),
        ]);

        $this->get('/blog/siguran-clanak')
            ->assertOk()
            ->assertSee('Siguran tekst')
            ->assertSee('Dobar link')
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('javascript:', false)
            ->assertDontSee('alert', false);
    }

    public function test_dashboard_portfolio_image_uses_automatic_category_album(): void
    {
        [$owner] = $this->makePhotographers();
        $this->seed(CategorySeeder::class);
        $category = Category::firstOrFail();

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));

        $this->actingAs($owner);

        $this->get('/dashboard/portfolio-images/create')
            ->assertOk()
            ->assertSee('Kategorija')
            ->assertSee('Fotografija')
            ->assertDontSee('Alt tekst')
            ->assertDontSee('slug');

        app(PortfolioService::class)->addImage($owner->photographerProfile, $category, 'portfolio/test.webp');
        app(PortfolioService::class)->addImage($owner->photographerProfile, $category, 'portfolio/test-2.webp');

        $this->assertDatabaseHas('portfolio_albums', [
            'category_id' => $category->id,
            'photographer_profile_id' => $owner->photographerProfile->id,
        ]);
        $portfolioImage = $owner->photographerProfile->portfolioImages()->firstOrFail();
        $this->assertSame($category->name.' - Owner Fotograf', $portfolioImage->alt_text);
        $this->assertSame('portfolio/test.webp', $portfolioImage->image_path);
        $this->assertSame(1, $owner->photographerProfile->albums()->count());
        $this->assertSame(2, $owner->photographerProfile->portfolioImages()->count());
        $this->assertTrue($owner->photographerProfile->categories()->whereKey($category->id)->exists());
    }

    public function test_dashboard_blog_create_ignores_tampered_profile_id(): void
    {
        [$owner, $other] = $this->makePhotographers();

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));

        $this->actingAs($owner);

        Livewire::test(CreatePhotographerBlogPostPage::class)
            ->fillForm([
                'photographer_profile_id' => $other->photographerProfile->id,
                'title' => 'Tamper Blog',
                'content' => '<p>Tekst</p>',
                'status' => PhotographerBlogStatus::Draft->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('photographer_blog_posts', [
            'title' => 'Tamper Blog',
            'slug' => 'tamper-blog',
            'photographer_profile_id' => $owner->photographerProfile->id,
        ]);
        $this->assertDatabaseMissing('photographer_blog_posts', [
            'title' => 'Tamper Blog',
            'photographer_profile_id' => $other->photographerProfile->id,
        ]);
    }

    public function test_filtered_search_uses_noindex_and_base_canonical(): void
    {
        $this->seed([LocationSeeder::class, CategorySeeder::class]);

        $this->get('/fotografi?city=banja-luka&date=2026-06-01')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('search').'">', false);

        $this->get('/fotografi')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('search').'">', false);
    }

    public function test_robots_blocks_private_panels(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /dashboard')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_google_analytics_and_search_console_are_configurable(): void
    {
        config()->set('services.google.analytics_id', 'G-ABC123DEF4');
        config()->set('services.google.site_verification', 'google-verification-token');

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="google-site-verification" content="google-verification-token">', false)
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=', false)
            ->assertSee("window.fotoMajstorAnalyticsId = 'G-ABC123DEF4'", false)
            ->assertSee('Prihvati')
            ->assertSee('Samo nužni')
            ->assertSee('Pročitaj više')
            ->assertDontSee('Prihvati analitiku')
            ->assertDontSee('<script async src="https://www.googletagmanager.com/gtag/js?id=G-ABC123DEF4"', false);
    }

    public function test_invalid_google_analytics_id_does_not_render_tracking_script(): void
    {
        config()->set('services.google.analytics_id', 'javascript:alert(1)');
        config()->set('services.google.site_verification', '');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag/js', false)
            ->assertDontSee('javascript:alert', false)
            ->assertDontSee('google-site-verification', false);
    }

    public function test_legal_pages_and_footer_links_are_public(): void
    {
        $this->get('/politika-privatnosti')
            ->assertOk()
            ->assertSee('Politika privatnosti')
            ->assertSee('Google Analytics')
            ->assertSee('Vaša prava');

        $this->get('/uslovi-koriscenja')
            ->assertOk()
            ->assertSee('Uslovi korištenja')
            ->assertSee('FotoMajstor je katalog')
            ->assertSee('ne naplaćuje proviziju');

        $this->get('/')
            ->assertOk()
            ->assertSee(route('privacy'))
            ->assertSee(route('terms'))
            ->assertSee('Postavke kolačića');
    }

    /** @return array{0: User, 1: User} */
    private function makePhotographers(): array
    {
        $owner = User::create([
            'name' => 'Owner Fotograf',
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => UserRole::Photographer,
            'email_verified_at' => now(),
        ]);

        $other = User::create([
            'name' => 'Other Fotograf',
            'email' => 'other@example.com',
            'password' => 'password',
            'role' => UserRole::Photographer,
            'email_verified_at' => now(),
        ]);

        return [$owner->refresh(), $other->refresh()];
    }
}
