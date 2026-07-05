<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminEmailUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return (new MailMessage)
            ->subject(__('notification.email_updated_notification_subject', [], $locale))
            ->greeting(__('notification.hello', ['name' => $notifiable->name], $locale).'!')
            ->line(__('notification.your_email_has_been_updated', [], $locale))
            ->line(__('notification.new_email_colon', [], $locale).' '.$notifiable->email)
            ->action(__('notification.login_to_dashboard', [], $locale), url('/admin/login'))
            ->line(__('notification.thank_you_for_using_our_application', [], $locale));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
