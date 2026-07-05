<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminWelcomeNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected string $password)
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
            ->subject(__('notification.welcome_to_dashboard', ['name' => config('app.name')], $locale))
            ->greeting(__('notification.hello', ['name' => $notifiable->name], $locale).'!')
            ->line(__('notification.you_have_been_registered_as_admin', [], $locale))
            ->line(__('notification.you_can_login_using_the_following_credentials', [], $locale))
            ->line(__('notification.email_colon').' '.$notifiable->email)
            ->line(__('notification.password_colon').' '.$this->password)
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
