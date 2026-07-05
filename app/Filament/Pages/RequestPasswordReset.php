<?php

namespace App\Filament\Pages;

use App\Notifications\ResetPasswordNotification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Exception;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        try {
            $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
                $data,
                function (CanResetPassword $user, string $token): void {
                    if (! method_exists($user, 'notify')) {
                        $userClass = $user::class;
                        throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                    }
                    if (! $user->is_active) {
                        $userClass = $user::class;

                        Notification::make()
                            ->title(__('admin_not_active'))
                            ->danger()
                            ->send();

                        throw new Halt;
                    }

                    $notification = new ResetPasswordNotification($token);
                    $user->notify($notification);
                },
            );
        } catch (Halt $exception) {
            return;
        }

        if ($status !== Password::RESET_LINK_SENT) {
            Notification::make()
                ->title(__($status))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__($status))
            ->success()
            ->send();

        $this->form->fill();
    }
}
