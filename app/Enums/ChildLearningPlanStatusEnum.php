<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum ChildLearningPlanStatusEnum: string
{
    // 'in_progress', 'completed', 'archived'

    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';

    public static function options(): Collection
    {
        return collect([
            'in_progress' => __('In Progress'),
            'completed' => __('Completed'),
            'archived' => __('Archived'),
        ]);
    }

    public static function label(self $status): string
    {
        return match ($status) {
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
            self::Archived => __('Archived'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Archived => 'danger',
        };
    }
}
