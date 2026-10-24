<?php

namespace App\Filament\Resources\LearningPlans\Schemas;

use Filament\Schemas\Components\Callout;
use Filament\Support\Icons\Heroicon;

/**
 * Plain-words guide to how learning content fits together, shown at the top of the plan, goal and lesson forms.
 */
class LearningStructureHint
{
    public static function make(string $level): Callout
    {
        $current = match ($level) {
            'plan' => __('You are editing a plan: the program assigned to a child. It groups goals and sets the daily limits.'),
            'goal' => __('You are editing a goal: a skill to learn. It can belong to several plans and contains lessons.'),
            default => __('You are editing a lesson: one step of a goal. It contains the exercises the child actually plays.'),
        };

        return Callout::make(__('How learning content is organized'))
            ->icon(Heroicon::OutlinedLightBulb)
            ->description(__('Plan → Goals → Lessons → Exercises. The child goes through them in display priority order (1 first), and each one opens after the previous one is completed.').' '.$current)
            ->info()
            ->columnSpanFull();
    }
}
