<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SupportTicketStatusEnum: string implements HasColor, HasLabel
{
    case OPEN = 'Open';
    case IN_PROGRESS = 'In Progress';
    case REPLIED = 'Replied';
    case CLOSED = 'Closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OPEN => __('Open'),
            self::IN_PROGRESS => __('In Progress'),
            self::REPLIED => __('Replied'),
            self::CLOSED => __('Closed'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            // New tickets need attention; closed ones fade into the background.
            self::OPEN => 'danger',
            self::IN_PROGRESS => 'warning',
            self::REPLIED => 'info',
            self::CLOSED => 'gray',
        };
    }

    public static function options(): array
    {
        return [
            self::OPEN->value => __('Open'),
            self::IN_PROGRESS->value => __('In Progress'),
            self::REPLIED->value => __('Replied'),
            self::CLOSED->value => __('Closed'),
        ];
    }
}
