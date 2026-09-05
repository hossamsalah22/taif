<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class SupportTicketRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected SupportTicket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'reference_number' => $this->ticket->reference_number,
            'message' => __('Your support ticket :ref has been replied to.', ['ref' => $this->ticket->reference_number]),
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: __('Support Ticket Update'),
            body: __('Your support ticket :ref has been replied to.', ['ref' => $this->ticket->reference_number])
        )))
            ->data(['ticket_id' => (string) $this->ticket->id]);
    }
}
