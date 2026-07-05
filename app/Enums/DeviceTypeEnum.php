<?php

namespace App\Enums;

enum DeviceTypeEnum: string
{
    case IOS = 'ios';

    case ANDROID = 'android';

    case WEB = 'web';

    case TABLET = 'tablet';

    case OTHER = 'other';
}
