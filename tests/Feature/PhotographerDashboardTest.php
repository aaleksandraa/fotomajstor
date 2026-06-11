<?php

namespace Tests\Feature;

use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Filament\Dashboard\Pages\Availability;
use App\Filament\Dashboard\Pages\EditProfile;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\Pages\EditPortfolioAlbum;
use App\Filament\Dashboard\Resources\PortfolioAlbumResource\RelationManagers\ImagesRelationManager;
use App\Http\Responses\DashboardLoginResponse;
use App\Models\Category;
use App\Models\User;
use App\Services\PortfolioService;
use Database\Seeders\CategorySeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('<meta name="robots" content="index, follow">', false);

        auth()->logout();

        $this->get(route('photographer.show', $profile))->assertOk();
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
        $profile = $this->photographer->photographerProfile;

        Livewire::test(Availability::class)
            ->assertSee('Upravljajte zauzetim terminima')
            ->assertSee('Naredni zauzeti termini')
            ->assertSee('Označi kao zauzeto')
            ->call('setDateStatus', $date, true);

        $this->get('/dashboard/availability')
            ->assertOk()
            ->assertSee('fullcalendar@6.1.20/index.global.min.js', false)
            ->assertSee('js/availability-calendar.js', false);

        $this->assertTrue($profile->unavailableDates()->whereDate('date', $date)->exists());
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
            ->assertSee('Otvori album');

        $this->get("/dashboard/portfolio-albums/{$album->id}/edit")
            ->assertOk()
            ->assertDontSee('Alt tekst');

        $other = User::factory()->create(['role' => UserRole::Photographer]);
        $this->actingAs($other);
        $this->assertFalse(PortfolioAlbumResource::getEloquentQuery()->whereKey($album->id)->exists());
        $this->assertFalse(ImagesRelationManager::canViewForRecord($album, EditPortfolioAlbum::class));
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
    }
}
