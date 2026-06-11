<?php

namespace Tests\Feature;

use App\Enums\ProfileType;
use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Filament\Dashboard\Pages\Auth\EmailVerificationPrompt;
use App\Filament\Dashboard\Pages\Auth\Register;
use App\Models\City;
use App\Models\User;
use App\Services\ImageService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Filament\Pages\Auth\PasswordReset\ResetPassword as ResetPasswordPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AuthLocaleWebpTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_service_converts_upload_to_webp(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg', 2400, 1600);

        $path = app(ImageService::class)->storeWebp($file, 'profiles', 800);

        $this->assertStringEndsWith('.webp', $path);
        $this->assertStringStartsWith('profiles/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_language_switch_persists_in_session(): void
    {
        $this->get('/jezik/bs');
        $this->assertSame('bs', session('locale'));

        // Nepodržan jezik se ignoriše.
        $this->get('/jezik/xx');
        $this->assertSame('bs', session('locale'));

        $this->get('/en');
        $this->assertSame('en', session('locale'));

        $this->get('/de');
        $this->assertSame('de', session('locale'));
    }

    public function test_locale_middleware_falls_back_to_primary_bhs_locale(): void
    {
        $this->withSession(['locale' => 'xx'])
            ->get('/')
            ->assertOk()
            ->assertSee('lang="bs"', false);
    }

    public function test_localized_public_page_has_canonical_and_hreflang_links(): void
    {
        $this->get('/it')
            ->assertOk()
            ->assertSee('lang="it"', false)
            ->assertSee('<link rel="canonical" href="'.url('/it').'">', false)
            ->assertSee('<link rel="alternate" hreflang="de" href="'.url('/de').'">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/').'">', false);
    }

    public function test_dashboard_registration_page_loads(): void
    {
        $this->seed(LocationSeeder::class);
        $this->seed(CategorySeeder::class);

        $this->get('/dashboard/register')->assertOk();
    }

    public function test_dashboard_registration_keeps_selected_locale(): void
    {
        $this->seed(LocationSeeder::class);

        $this->get('/it');

        $this->get('/dashboard/register')
            ->assertOk()
            ->assertSee('lang="it"', false)
            ->assertSee('Tipo di profilo')
            ->assertSee('Bosnia ed Erzegovina');
    }

    public function test_catalog_names_are_localized(): void
    {
        $this->seed([LocationSeeder::class, CategorySeeder::class]);

        $this->get('/de/kategorije')
            ->assertOk()
            ->assertSee('Hochzeiten')
            ->assertSee('Produktfotografie');

        $this->get('/en/gradovi')
            ->assertOk()
            ->assertSee('Bosnia and Herzegovina')
            ->assertSee('Croatia');
    }

    public function test_registration_creates_photographer_with_enriched_profile(): void
    {
        Notification::fake();
        $this->seed(LocationSeeder::class);

        $city = City::query()->firstOrFail();

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Marko Marković',
                'display_name' => 'Marko Foto Studio',
                'profile_type' => ProfileType::Company->value,
                'service_type' => ServiceType::PhotographerVideographer->value,
                'primary_city_id' => $city->id,
                'email' => 'marko@example.com',
                'password' => 'tajna-lozinka-123',
                'passwordConfirmation' => 'tajna-lozinka-123',
            ])
            ->call('register')
            ->assertRedirect('/dashboard/email-verification/prompt')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'marko@example.com')->firstOrFail();
        $this->assertSame(UserRole::Photographer, $user->role);

        $profile = $user->photographerProfile()->firstOrFail();
        $this->assertSame('Marko Foto Studio', $profile->display_name);
        $this->assertSame(ProfileType::Company, $profile->profile_type);
        $this->assertSame(ServiceType::PhotographerVideographer, $profile->service_type);
        $this->assertSame($city->id, $profile->primary_city_id);
        $this->assertSame($city->country_id, $profile->primary_country_id);
        $this->assertFalse((bool) $profile->active, 'Novi profil mora biti neaktivan do odobrenja.');
        $this->assertTrue($profile->cities()->whereKey($city->id)->exists());
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->get('/dashboard/email-verification/prompt')
            ->assertOk()
            ->assertSee('Registracija je uspješna.')
            ->assertSee('Provjerite svoj e-mail i kliknite na link za potvrdu profila.');
    }

    public function test_unverified_photographer_is_sent_to_email_verification_prompt(): void
    {
        $user = User::factory()->unverified()->create(['role' => UserRole::Photographer]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/dashboard/email-verification/prompt');

        $this->get('/dashboard/email-verification/prompt')
            ->assertOk()
            ->assertSee('Potvrdite e-mail adresu')
            ->assertSee('Poslali smo poruku na')
            ->assertSee('Niste primili e-mail?')
            ->assertSee('Pošalji ponovo')
            ->assertSee('Nazad na web stranicu')
            ->assertSee('Odjavi se')
            ->assertDontSee('filament-panels::', false);
    }

    public function test_public_navigation_uses_dashboard_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::Photographer]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('href="'.url('/dashboard').'"', false)
            ->assertDontSee('Postani fotograf');
    }

    public function test_public_navigation_uses_registration_for_guest(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Postani fotograf')
            ->assertDontSee('href="'.url('/dashboard').'"', false);
    }

    public function test_verification_resend_smtp_failure_shows_notification_instead_of_server_error(): void
    {
        $user = User::factory()->unverified()->create(['role' => UserRole::Photographer]);

        config()->set('queue.default', 'sync');
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', '127.0.0.1');
        config()->set('mail.mailers.smtp.port', 1);
        config()->set('mail.mailers.smtp.timeout', 1);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        $this->actingAs($user);

        Livewire::test(EmailVerificationPrompt::class)
            ->callAction('resendNotification')
            ->assertNotified('E-mail trenutno nije moguće poslati');
    }

    public function test_registration_succeeds_when_initial_verification_delivery_fails(): void
    {
        $this->seed(LocationSeeder::class);
        $city = City::query()->firstOrFail();

        config()->set('queue.default', 'sync');
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', '127.0.0.1');
        config()->set('mail.mailers.smtp.port', 1);
        config()->set('mail.mailers.smtp.timeout', 1);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'SMTP Test',
                'profile_type' => ProfileType::Individual->value,
                'service_type' => ServiceType::Photographer->value,
                'primary_city_id' => $city->id,
                'email' => 'smtp-test@example.com',
                'password' => 'tajna-lozinka-123',
                'passwordConfirmation' => 'tajna-lozinka-123',
            ])
            ->call('register')
            ->assertRedirect('/dashboard/email-verification/prompt')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'smtp-test@example.com']);
    }

    public function test_photographer_can_request_password_reset_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => UserRole::Photographer]);
        Filament::setCurrentPanel(Filament::getPanel('dashboard'));

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        Livewire::test(ResetPasswordPage::class, ['email' => $user->email, 'token' => $token])
            ->fillForm([
                'password' => 'nova-sigurna-lozinka-123',
                'passwordConfirmation' => 'nova-sigurna-lozinka-123',
            ])
            ->call('resetPassword')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('nova-sigurna-lozinka-123', $user->fresh()->password));
    }

    public function test_individual_registration_uses_personal_name_as_display_name(): void
    {
        $this->seed(LocationSeeder::class);

        $city = City::query()->firstOrFail();

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Ana Anić',
                'profile_type' => ProfileType::Individual->value,
                'service_type' => ServiceType::Photographer->value,
                'primary_city_id' => $city->id,
                'email' => 'ana@example.com',
                'password' => 'tajna-lozinka-123',
                'passwordConfirmation' => 'tajna-lozinka-123',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $profile = User::where('email', 'ana@example.com')->firstOrFail()->photographerProfile()->firstOrFail();

        $this->assertSame(ProfileType::Individual, $profile->profile_type);
        $this->assertSame('Ana Anić', $profile->display_name);
    }
}
