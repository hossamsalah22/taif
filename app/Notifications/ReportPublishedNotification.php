<?php

namespace App\Notifications;

use App\Models\AssessmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class ReportPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const MESSAGE = "Your child's screening assessment report is ready. Tap to view the official specialist analysis.";

    public function __construct(public AssessmentSubmission $submission) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'message_key' => self::MESSAGE,
            'message' => __(self::MESSAGE),
        ];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: __('Assessment Report'),
            body: __(self::MESSAGE),
        )))
            ->data([
                'submission_id' => (string) $this->submission->id,
                'child_id' => (string) $this->submission->child_id,
            ]);
    }
}
