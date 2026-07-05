<?php

namespace App\Services;

use NotificationChannels\Fcm\FcmMessage;

class FcmMessageBuilder
{
    public static function createWithDefaults(): FcmMessage
    {
        return FcmMessage::create()
            ->custom([
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
            ]);
    }
}
