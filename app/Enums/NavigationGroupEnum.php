<?php

namespace App\Enums;

use Filament\Navigation\NavigationGroup;
use Filament\Support\Contracts\HasLabel;

/**
 * Admin sidebar sections, in the order they are shown. Each section is one area of work,
 * so an entry lives in exactly one place: content, students, finance, settings and reports.
 */
enum NavigationGroupEnum: string implements HasLabel
{
    case CONTENT = 'content';
    case STUDENTS = 'students';
    case FINANCE = 'finance';
    case SETTINGS = 'settings';
    case REPORTS = 'reports';

    public function getLabel(): string
    {
        return match ($this) {
            self::CONTENT => __('Content'),
            self::STUDENTS => __('Students'),
            self::FINANCE => __('Finance & subscriptions'),
            self::SETTINGS => __('Settings'),
            self::REPORTS => __('Reports'),
        };
    }

    /**
     * Settings are rarely changed, so that section starts collapsed and the daily work stays on screen.
     */
    public function isCollapsedByDefault(): bool
    {
        return $this === self::SETTINGS;
    }

    /**
     * Groups for the panel, keyed by case name so resources can reference the enum case directly.
     * Labels are closures so they follow the locale of the current request.
     *
     * @return array<string, NavigationGroup>
     */
    public static function navigationGroups(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $group): array => [
                $group->name => NavigationGroup::make()
                    ->label(fn (): string => $group->getLabel())
                    ->collapsed($group->isCollapsedByDefault())
                    ->collapsible(),
            ])
            ->all();
    }
}
