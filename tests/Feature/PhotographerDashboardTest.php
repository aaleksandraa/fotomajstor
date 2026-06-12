<?php

namespace Tests\Feature;

use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Filament\Dashboard\Pages\Availability;
use App\Filament\Dashboard\Pages\EditProfile;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages\EditPortfolioAlbum;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages\ListPortfolioAlbums;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers\ImagesRelationManager;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers\VideosRelationManager;
use App\Http\Responses\DashboardEmailVerificationResponse;
use App\Http\Responses\DashboardLoginResponse;
use App\Models\Category;
use App\Models\PortfolioVideo;
use App\Models\User;
use App\Services\PortfolioService;
use Database\Seeders\CategorySeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class PhotographerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $photographer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photographer = User::create([
            'name' => 'Test Fotograf',
            'email' => 'fotograf@example.com',
            'email_verified_at' => now(),
            'password' => 'password',
            'role' => UserRole::Photographer,
        ]);

        $this->photographer->photographerProfile()->update([
            'phone' => '+387 65 123 456',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        $this->actingAs($this->photographer);
    }

    public function test_dashboard_does_not_show_filament_documentation_or_github_widget(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Documentation')
            ->assertDontSee('github.com/filamentphp/filament', false)
            ->assertSee('Pregledaj moj profil')
            ->assertSee('Otvori web stranicu');
    }

    public function test_verified_photographer_profile_is_public(): void
    {
        $profile = $this->photographer->photographerProfile;

        $this->get(route('photographer.show', $profile))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertDontSee('Pregled vašeg profila')
            ->assertDontSee('Profil još nije javno objavljen');

        auth()->logout();

        $this->get(route('photographer.show', $profile))
            ->assertOk()
            ->assertDontSee('Pregled vašeg profila');
    }

    public function test_email_verification_automatically_publishes_profile_and_opens_profile_editor(): void
    {
        $user = User::factory()->unverified()->create(['role' => UserRole::Photographer]);
        $this->actingAs($user);

        $this->assertFalse($user->photographerProfile->active);
        $this->assertFalse($user->photographerProfile->verified);

        $verificationUrl = URL::temporarySignedRoute(
            'filament.dashboard.auth.email-verification.verify',
            now()->addMinutes(30),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->get($verificationUrl)->assertRedirect('/dashboard/edit-profile');

        $profile = $user->photographerProfile()->firstOrFail();
        $this->assertTrue($profile->active);
        $this->assertTrue($profile->verified);
        $this->assertNotNull($user->fresh()->email_verified_at);

        auth()->logout();

        $this->get(route('photographer.show', $profile))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false);

        $this->get(route('search'))
            ->assertOk()
            ->assertSee($profile->display_name);
    }

    public function test_verification_response_repairs_an_inactive_verified_profile(): void
    {
        $profile = $this->photographer->photographerProfile;
        $profile->update(['active' => false, 'verified' => false]);

        app(DashboardEmailVerificationResponse::class)->toResponse(request());

        $profile->refresh();
        $this->assertTrue($profile->active);
        $this->assertTrue($profile->verified);
    }

    public function test_first_login_opens_profile_editor_until_profile_is_saved(): void
    {
        $response = app(DashboardLoginResponse::class)->toResponse(request());

        $this->assertSame(url('/dashboard/edit-profile'), $response->getTargetUrl());

        $this->photographer->photographerProfile()->update(['onboarding_completed_at' => now()]);

        $response = app(DashboardLoginResponse::class)->toResponse(request());

        $this->assertSame(url('/dashboard'), $response->getTargetUrl());
    }

    public function test_photographer_can_toggle_and_bulk_edit_availability(): void
    {
        $date = now()->addDays(3)->toDateString();
        $secondDate = now()->addDays(4)->toDateString();
        $profile = $this->photographer->photographerProfile;

        Livewire::test(Availability::class)
            ->assertSee('Upravljajte zauzetim terminima')
            ->assertSee('Naredni zauzeti termini')
            ->assertSee('Zauzet dan')
            ->call('setDateStatus', $date, true)
            ->call('setDateStatus', $secondDate, true)
            ->call('setDateStatus', $date, false)
            ->call('setDateStatus', $date, true);

        $this->get('/dashboard/availability')
            ->assertOk()
            ->assertDontSee('fullcalendar@6.1.20', false)
            ->assertSee('vendor/vanilla-calendar-pro/index.css', false)
            ->assertSee('js/availability-calendar.js', false)
            ->assertSee('wire:key="availability-calendar-stable"', false)
            ->assertSee('availability-open-date', false)
            ->assertSee('availability-mark-month', false)
            ->assertSee('handleCalendarClick($event)', false)
            ->assertSee('applyDateStatus(false)', false)
            ->assertSee('applyDateStatus(true)', false)
            ->assertSee('availability-status-confirmed', false)
            ->assertSee('data-availability-state', false)
            ->assertSee('Slobodan dan')
            ->assertSee('Zauzet dan')
            ->assertSee('Slobodan termin')
            ->assertSee('Zauzet termin')
            ->assertDontSee('Današnji datum')
            ->assertSee('bs-BA', false);

        $this->assertFileExists(public_path('vendor/vanilla-calendar-pro/index.mjs'));

        $this->withSession(['locale' => 'sr'])
            ->get('/dashboard/availability')
            ->assertOk()
            ->assertSee('sr-Latn-RS', false);

        $this->assertTrue($profile->unavailableDates()->whereDate('date', $date)->exists());
        $this->assertTrue($profile->unavailableDates()->whereDate('date', $secondDate)->exists());
        $this->get(route('photographer.show', $profile))
            ->assertOk()
            ->assertSee('bg-ink-50 text-ink-300 line-through', false);

        Livewire::test(Availability::class)
            ->call('setDateStatus', $date, false);

        $this->assertFalse($profile->unavailableDates()->whereDate('date', $date)->exists());

        Livewire::test(Availability::class)->call('markMonthUnavailable');
        $this->assertTrue($profile->unavailableDates()->exists());

        Livewire::test(Availability::class)->call('markMonthAvailable');
        $this->assertFalse($profile->unavailableDates()->exists());
    }

    public function test_photographer_can_change_service_type(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm([
                'service_type' => ServiceType::PhotographerVideographer->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ServiceType::PhotographerVideographer,
            $this->photographer->photographerProfile()->firstOrFail()->service_type,
        );
        $this->assertNotNull($this->photographer->photographerProfile()->firstOrFail()->onboarding_completed_at);
    }

    public function test_profile_can_be_saved_before_onboarding_migration_is_run(): void
    {
        Schema::shouldReceive('hasColumn')
            ->once()
            ->with('photographer_profiles', 'onboarding_completed_at')
            ->andReturnFalse();

        Livewire::test(EditProfile::class)
            ->fillForm([
                'display_name' => 'Novi javni naziv',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            'Novi javni naziv',
            $this->photographer->photographerProfile()->value('display_name'),
        );
    }

    public function test_portfolio_is_managed_as_category_albums_with_bulk_upload_and_reordering(): void
    {
        $this->seed(CategorySeeder::class);
        $category = Category::firstOrFail();
        $profile = $this->photographer->photographerProfile;

        $album = app(PortfolioService::class)->addImages($profile, $category, [
            'portfolio/first.webp',
            'portfolio/second.webp',
            'portfolio/third.webp',
        ]);

        $this->get('/dashboard/portfolio-albums/create')
            ->assertOk()
            ->assertSee('Kategorija albuma')
            ->assertSee('Fotografije')
            ->assertDontSee('Alt tekst');

        $this->get('/dashboard/portfolio-albums')
            ->assertOk()
            ->assertSee($category->name)
            ->assertSee('Otvori album')
            ->assertSee('Dodaj video')
            ->assertSee('Broj videa');

        $this->get("/dashboard/portfolio-albums/{$album->id}/edit")
            ->assertOk()
            ->assertDontSee('Alt tekst')
            ->assertSee('Video zapisi');

        $other = User::factory()->create(['role' => UserRole::Photographer]);
        $this->actingAs($other);
        $this->assertFalse(PortfolioAlbumResource::getEloquentQuery()->whereKey($album->id)->exists());
        $this->assertFalse(ImagesRelationManager::canViewForRecord($album, EditPortfolioAlbum::class));
        $this->assertFalse(VideosRelationManager::canViewForRecord($album, EditPortfolioAlbum::class));
        $this->actingAs($this->photographer);

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $album,
            'pageClass' => EditPortfolioAlbum::class,
        ])
            ->assertTableActionExists('uploadImages')
            ->callTableAction('uploadImages', data: [
                'image_paths' => ['portfolio/fourth.webp', 'portfolio/fifth.webp'],
            ])
            ->assertHasNoTableActionErrors();

        $images = $album->images()->orderBy('sort_order')->get();
        $this->assertCount(5, $images);
        $this->assertSame([1, 2, 3, 4, 5], $images->pluck('sort_order')->all());

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $album,
            'pageClass' => EditPortfolioAlbum::class,
        ])->call('reorderTable', $images->pluck('id')->reverse()->values()->all());

        $this->assertSame(
            $images->pluck('id')->reverse()->values()->all(),
            $album->images()->orderBy('sort_order')->pluck('id')->all(),
        );

        Livewire::test(VideosRelationManager::class, [
            'ownerRecord' => $album,
            'pageClass' => EditPortfolioAlbum::class,
        ])
            ->assertTableActionExists('create')
            ->callTableAction('create', data: [
                'url' => 'https://vimeo.com/123456789',
                'title' => 'Video iz albuma',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas(PortfolioVideo::class, [
            'portfolio_album_id' => $album->id,
            'provider' => 'vimeo',
            'provider_video_id' => '123456789',
            'sort_order' => 1,
        ]);

        $videoCategory = Category::whereKeyNot($category->id)->firstOrFail();

        Livewire::test(ListPortfolioAlbums::class)
            ->assertActionExists('addVideo')
            ->callAction('addVideo', data: [
                'category_id' => $videoCategory->id,
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'title' => 'Video bez fotografija',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(PortfolioVideo::class, [
            'portfolio_album_id' => $profile->albums()->where('category_id', $videoCategory->id)->value('id'),
            'provider' => 'youtube',
            'provider_video_id' => 'dQw4w9WgXcQ',
        ]);
        $this->assertTrue($profile->categories()->whereKey($videoCategory->id)->exists());

        $this->get(route('photographer.show', $profile))
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('Video bez fotografija');
    }
}
