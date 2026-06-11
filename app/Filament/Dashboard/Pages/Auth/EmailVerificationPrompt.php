<?php

namespace App\Filament\Dashboard\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailVerificationPrompt extends BaseEmailVerificationPrompt
{
    public function resendNotificationAction(): Action
    {
        return Action::make('resendNotification')
            ->link()
            ->label(__('filament-panels::pages/auth/email-verification/email-verification-prompt.actions.resend_notification.label').'.')
            ->action(function (): void {
                try {
                    $this->rateLimit(2);
                } catch (TooManyRequestsException $exception) {
                    $this->getRateLimitedNotification($exception)?->send();

                    return;
                }

                try {
                    $this->sendEmailVerificationNotification($this->getVerifiable());
                } catch (Throwable $exception) {
                    Log::error('Email verification resend failed.', [
                        'user_id' => auth()->id(),
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);

                    Notification::make()
                        ->title(__('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_failed.title'))
                        ->body(__('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_failed.body'))
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resent.title'))
                    ->success()
                    ->send();
            });
    }
}
