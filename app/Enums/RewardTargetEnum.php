<?php

namespace App\Enums;

enum RewardTargetEnum: string
{
    case QUESTION = 'question';
    case LESSON = 'lesson';
    case GOAL = 'goal';
    case PLAN = 'plan';

    public static function label(self $target): string
    {
        return match ($target) {
            self::QUESTION => __('Question'),
            self::LESSON => __('Lesson'),
            self::GOAL => __('Goal'),
            self::PLAN => __('Learning Plan'),
        };
    }

    public static function colors(self $target): string
    {
        return match ($target) {
            self::QUESTION => 'info',
            self::LESSON => 'primary',
            self::GOAL => 'success',
            self::PLAN => 'warning',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => self::label($case)])
            ->all();
    }
}
