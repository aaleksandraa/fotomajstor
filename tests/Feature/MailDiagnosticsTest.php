<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_diagnostics_reports_delivery_and_queue_configuration_without_secrets(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', 'mail.example.com');
        config()->set('mail.mailers.smtp.port', 587);
        config()->set('mail.mailers.smtp.username', 'info@example.com');
        config()->set('mail.mailers.smtp.password', 'secret-value');
        config()->set('queue.default', 'database');

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('mail.example.com')
            ->expectsOutputToContain('configured')
            ->expectsOutputToContain('Queued verification and password-reset emails require a running queue:work process.')
            ->doesntExpectOutputToContain('secret-value')
            ->assertSuccessful();
    }

    public function test_mail_diagnostics_rejects_invalid_test_recipient(): void
    {
        $this->artisan('mail:diagnose --send=not-an-email')
            ->expectsOutputToContain('must contain a valid email address')
            ->assertExitCode(2);
    }
}
