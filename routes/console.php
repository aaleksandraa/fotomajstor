<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:diagnose {--send= : Optional recipient for a direct SMTP test}', function (): int {
    $mailer = config('mail.default');
    $smtp = config('mail.mailers.smtp');
    $queue = config('queue.default');

    $this->table(['Setting', 'Value'], [
        ['Mail driver', $mailer],
        ['SMTP host', $smtp['host'] ?? 'not set'],
        ['SMTP port', $smtp['port'] ?? 'not set'],
        ['SMTP scheme', $smtp['scheme'] ?: 'auto'],
        ['Require TLS', ($smtp['require_tls'] ?? false) ? 'yes' : 'no'],
        ['SMTP username', filled($smtp['username'] ?? null) ? 'configured' : 'not set'],
        ['SMTP password', filled($smtp['password'] ?? null) ? 'configured' : 'not set'],
        ['From address', config('mail.from.address')],
        ['Queue connection', $queue],
        ['Pending jobs', Schema::hasTable(config('queue.connections.database.table', 'jobs')) ? DB::table(config('queue.connections.database.table', 'jobs'))->count() : 'table missing'],
        ['Failed jobs', Schema::hasTable(config('queue.failed.table', 'failed_jobs')) ? DB::table(config('queue.failed.table', 'failed_jobs'))->count() : 'table missing'],
    ]);

    if ($mailer !== 'smtp') {
        $this->warn('MAIL_MAILER is not smtp, so real emails will not be delivered through SMTP.');
    }

    if ($queue === 'database') {
        $this->warn('Queued verification and password-reset emails require a running queue:work process.');
    }

    if (($smtp['port'] ?? null) === 2525) {
        $this->warn('Port 2525 is provider-specific. Confirm that your SMTP server accepts connections on this port.');
    }

    if (($smtp['port'] ?? null) === 587 && ! ($smtp['require_tls'] ?? false)) {
        $this->warn('Port 587 should use MAIL_REQUIRE_TLS=true.');
    }

    $recipient = $this->option('send');
    if (blank($recipient)) {
        $this->info('Configuration check complete. Use --send=email@example.com for a direct SMTP delivery test.');

        return Command::SUCCESS;
    }

    if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
        $this->error('The --send option must contain a valid email address.');

        return Command::INVALID;
    }

    try {
        Mail::raw('FotoMajstor SMTP dijagnostička poruka.', function ($message) use ($recipient): void {
            $message->to($recipient)->subject('FotoMajstor SMTP test');
        });
    } catch (Throwable $exception) {
        $this->error('SMTP test failed: '.$exception->getMessage());

        return Command::FAILURE;
    }

    $this->info("SMTP test message sent directly to {$recipient}.");

    return Command::SUCCESS;
})->purpose('Inspect mail and queue configuration and optionally send a direct SMTP test');
