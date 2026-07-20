<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendUserOtpNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected string $otp, protected Carbon $expires_at) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            // TaqnyatChannel::class,
            'mail',
            'database',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return (new MailMessage)
            ->subject(__('notification.otp_email_subject', [], $locale))
            ->greeting(__('notification.otp_email_greeting', ['name' => $notifiable->name ?? ''], $locale))
            ->line(__('notification.otp_email_line', ['code' => $this->otp], $locale))
            ->line(__('notification.otp_email_expiry', ['time' => $this->expires_at->diffForHumans()], $locale))
            ->line(__('notification.otp_email_ignore', [], $locale));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => [
                'en' => __('notification.code_sent_successfully', ['phone' => $notifiable->phone], 'en'),
                'ar' => __('notification.code_sent_successfully', ['phone' => $notifiable->phone], 'ar'),
            ],
            'otp' => $this->otp,
        ];
    }

    /**
     * Get the Taqnyat representation of the notification.
     */
    public function toTaqnyat(object $notifiable): string
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return __('taqnyat.otp_message', ['code' => $this->otp], $locale);
    }
}
