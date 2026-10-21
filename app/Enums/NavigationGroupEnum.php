<?php

namespace App\Enums;

use Filament\Navigation\NavigationGroup;
use Filament\Support\Contracts\HasLabel;

/**
 * Admin sidebar groups, in the order they are shown: daily work first, rarely changed setup last.
 */
enum NavigationGroupEnum: string implements HasLabel
{
    case OPERATIONS = 'operations';
    case FAMILIES = 'families';
    case LEARNING_CONTENT = 'learning_content';
    case SUBSCRIPTIONS = 'subscriptions';
    case APP_CONTENT = 'app_content';
    case LOCATIONS = 'locations';
    case SETTINGS = 'settings';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPERATIONS => __('Daily operations'),
            self::FAMILIES => __('Children & families'),
            self::LEARNING_CONTENT => __('Learning content'),
            self::SUBSCRIPTIONS => __('Subscriptions'),
            self::APP_CONTENT => __('App content'),
            self::LOCATIONS => __('Locations'),
            self::SETTINGS => __('Settings & access'),
        };
    }

    /**
     * Setup groups that are rarely touched start collapsed so the daily work stays on screen.
     */
    public function isCollapsedByDefault(): bool
    {
        return in_array($this, [self::APP_CONTENT, self::LOCATIONS, self::SETTINGS], true);
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
