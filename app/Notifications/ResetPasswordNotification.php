<?php

namespace App\Notifications;

use Filament\Facades\Filament;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get(__('notification.reset_password_email_subject')))
            ->greeting(Lang::get(__('notification.hello', ['name' => $notifiable->name])))
            ->line(Lang::get(__('notification.you_are_receiving_this_email_because_we_received_a_password_reset_request_for_your_account')))
            ->action(Lang::get(__('notification.reset_password')), $this->resetUrl($notifiable))
            ->line(Lang::get(__('notification.this_password_reset_link_will_expire_in_minutes', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])))
            ->line(Lang::get(__('notification.if_you_did_not_request_a_password_reset_no_further_action_is_required')));
    }

    protected function resetUrl(mixed $notifiable): string
    {
        return Filament::getResetPasswordUrl($this->token, $notifiable);
    }
}
