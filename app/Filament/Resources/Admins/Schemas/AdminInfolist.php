<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Models\Admin;
use Filament\Facades\Filament;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class AdminInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin_profile_header'))
                    ->columnSpanFull()
                    ->components([
                        Tabs::make(__('admin_profile_tabs'))
                            ->tabs([
                                Tab::make(__('profile'))
                                    ->components([
                                        TextEntry::make('name')
                                            ->label(__('name')),
                                        TextEntry::make('email')
                                            ->label(__('email')),
                                        TextEntry::make('phone')
                                            ->label(__('phone')),
                                        TextEntry::make('roles.name')
                                            ->label(__('roles'))
                                            ->badge(),
                                        TextEntry::make('is_active')
                                            ->label(__('is_active'))
                                            ->formatStateUsing(fn ($state) => $state ? __('active') : __('suspended'))
                                            ->badge()
                                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                                        TextEntry::make('created_at')
                                            ->label(__('created_at'))
                                            ->dateTime(),
                                    ])->columns(2),
                                Tab::make(__('permissions'))
                                    ->schema([
                                        TextEntry::make('super_admin_permissions')
                                            ->hiddenLabel()
                                            ->state(__('A super admin can do everything in the panel.'))
                                            ->icon('heroicon-o-shield-check')
                                            ->color('success')
                                            ->visible(fn (Admin $record): bool => $record->isSuperAdmin()),
                                        TextEntry::make('no_permissions')
                                            ->hiddenLabel()
                                            ->state(__('This admin has no permissions yet. Give them a role to grant some.'))
                                            ->color('gray')
                                            ->visible(fn (Admin $record): bool => ! $record->isSuperAdmin() && self::permissionRows($record) === []),
                                        RepeatableEntry::make('permission_rows')
                                            ->hiddenLabel()
                                            ->state(fn (Admin $record): array => self::permissionRows($record))
                                            ->schema([
                                                TextEntry::make('section')
                                                    ->label(__('Section'))
                                                    ->weight('bold'),
                                                TextEntry::make('actions')
                                                    ->label(__('Allowed Actions'))
                                                    ->badge(),
                                            ])
                                            ->columns(2)
                                            ->visible(fn (Admin $record): bool => ! $record->isSuperAdmin() && self::permissionRows($record) !== []),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * The admin's permissions (from their roles and given directly), one row per section of the panel,
     * e.g. ['section' => 'الأطفال', 'actions' => ['عرض', 'تعديل']].
     *
     * @return array<int, array{section: string, actions: array<int, string>}>
     */
    public static function permissionRows(Admin $admin): array
    {
        $sectionLabels = self::sectionLabels();

        return $admin->getAllPermissions()
            ->map(fn (Permission $permission): array => explode(':', $permission->name, 2) + [1 => ''])
            ->groupBy(1)
            ->map(fn (Collection $permissions, string $subject): array => [
                'section' => $sectionLabels[$subject] ?? Str::headline($subject),
                'actions' => $permissions
                    ->map(fn (array $permission): string => self::actionLabel($permission[0]))
                    ->unique()
                    ->values()
                    ->all(),
            ])
            ->sortBy('section')
            ->values()
            ->all();
    }

    /**
     * Arabic names of the panel's resources and pages, keyed by the class name used in permissions ("Child", "ManageGeneral").
     *
     * @return array<string, string>
     */
    private static function sectionLabels(): array
    {
        $panel = Filament::getPanel('admin');

        $resources = collect($panel->getResources())
            ->mapWithKeys(fn (string $resource): array => [class_basename($resource::getModel()) => $resource::getPluralModelLabel()]);

        $pages = collect($panel->getPages())
            ->mapWithKeys(fn (string $page): array => [class_basename($page) => (string) $page::getNavigationLabel()]);

        return $resources->merge($pages)->all();
    }

    /**
     * "ViewAny" → Shield's translated label for view_any ("عرض الكل").
     */
    private static function actionLabel(string $action): string
    {
        $key = 'filament-shield::filament-shield.resource_permission_prefixes_labels.'.Str::snake($action);

        return Lang::has($key) ? __($key) : __(Str::headline($action));
    }
}
