<?php

namespace App\Enums;

enum AssessmentStatusEnum: string
{
    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case DRAFT = 'draft';

    public static function label(self $status): string
    {
        return match ($status) {
            self::ACTIVE => __('Active'),
            self::DEPRECATED => __('Deprecated'),
            self::DRAFT => __('Draft'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::ACTIVE => 'success',
            self::DEPRECATED => 'danger',
            self::DRAFT => 'warning',
        };
    }

    public static function options(): array
    {
        return [
            self::ACTIVE->value => __('Active'),
            self::DEPRECATED->value => __('Deprecated'),
            self::DRAFT->value => __('Draft'),
        ];
    }
}
