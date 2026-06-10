<?php

namespace Tests\Feature;

use App\Enums\BlogStatus;
use App\Enums\PhotographerBlogStatus;
use App\Enums\UserRole;
use App\Filament\Dashboard\Resources\PhotographerBlogPostResource\Pages\CreatePhotographerBlogPost as CreatePhotographerBlogPostPage;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages\CreatePortfolioAlbum as CreatePortfolioAlbumPage;
use App\Models\BlogPost;
use App\Models\User;
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

    public function test_dashboard_album_create_ignores_tampered_profile_id(): void
    {
        [$owner, $other] = $this->makePhotographers();

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));

        $this->actingAs($owner);

        Livewire::test(CreatePortfolioAlbumPage::class)
            ->fillForm([
                'photographer_profile_id' => $other->photographerProfile->id,
                'title' => 'Tamper Album',
                'slug' => 'tamper-album',
                'active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('portfolio_albums', [
            'title' => 'Tamper Album',
            'photographer_profile_id' => $owner->photographerProfile->id,
        ]);
        $this->assertDatabaseMissing('portfolio_albums', [
            'title' => 'Tamper Album',
            'photographer_profile_id' => $other->photographerProfile->id,
        ]);
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
                'slug' => 'tamper-blog',
                'content' => '<p>Tekst</p>',
                'status' => PhotographerBlogStatus::Draft->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('photographer_blog_posts', [
            'title' => 'Tamper Blog',
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
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-ABC123DEF4', false)
            ->assertSee("gtag('config', 'G-ABC123DEF4'", false);
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
