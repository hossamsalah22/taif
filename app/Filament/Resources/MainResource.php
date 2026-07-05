<?php

namespace App\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Illuminate\Support\Str;

abstract class MainResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function getLabel(): ?string
    {
        $model = class_basename(static::$model);

        return __((string) Str::of($model)
            ->snake()           // e.g. "GarageSpot" -> "garage_spot"
            ->replace('_', ' ') // -> "garage spot"
            ->lower());          // ensure all lowercase
    }

    public static function getPluralLabel(): ?string
    {
        $model = class_basename(static::$model);

        return __((string) Str::of(Str::plural($model))
            ->snake()
            ->replace('_', ' ')
            ->lower());
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
