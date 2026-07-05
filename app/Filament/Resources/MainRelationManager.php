<?php

namespace App\Filament\Resources;

use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class MainRelationManager extends RelationManager
{
    public static function getModelLabel(): string
    {
        $relationship = static::getRelationshipName();
        $relationship = Str::singular($relationship);

        return __(
            (string) Str::of($relationship)
                ->snake()
                ->replace('_', ' ')
        );
    }

    public static function getPluralModelLabel(): string
    {
        $relationship = static::getRelationshipName();

        return __(
            (string) Str::of($relationship)
                ->snake()
                ->replace('_', ' ')
        );
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        $relationship = static::getRelationshipName();

        return __(
            (string) Str::of($relationship)
                ->snake()
                ->replace('_', ' ')
        );
    }
}
