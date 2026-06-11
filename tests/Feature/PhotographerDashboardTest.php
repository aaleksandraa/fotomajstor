<?php

namespace Tests\Feature;

use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Filament\Dashboard\Pages\Availability;
use App\Filament\Dashboard\Pages\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_photographer_can_preview_own_inactive_profile_but_guest_cannot(): void
    {
        $profile = $this->photographer->photographerProfile;

        $this->get(route('photographer.show', $profile))
            ->assertOk()
            ->assertSee('Pregled vašeg profila')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);

        auth()->logout();

        $this->get(route('photographer.show', $profile))->assertNotFound();
    }

    public function test_photographer_can_toggle_and_bulk_edit_availability(): void
    {
        $date = now()->addDays(3)->toDateString();
        $profile = $this->photographer->photographerProfile;

        Livewire::test(Availability::class)
            ->call('toggleDate', $date);

        $this->assertTrue($profile->unavailableDates()->whereDate('date', $date)->exists());

        Livewire::test(Availability::class)
            ->call('toggleDate', $date);

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
    }
}
