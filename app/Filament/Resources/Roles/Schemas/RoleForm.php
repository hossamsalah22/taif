<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Rules\Unique;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()->schema([
                    TextInput::make('name')
                        ->label(__('filament-shield::filament-shield.field.name'))
                        ->unique(
                            ignoreRecord: true,
                            /** @phpstan-ignore-next-line */
                            modifyRuleUsing: fn (Unique $rule) => Utils::isTenancyEnabled() ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id) : $rule
                        )
                        ->rule(['required', 'max:255']),

                    TextInput::make('guard_name')
                        ->label(__('filament-shield::filament-shield.field.guard_name'))
                        ->default(Utils::getFilamentAuthGuard())
                        ->nullable()
                        ->hidden()
                        ->rule('max:255'),

                    Select::make(config('permission.column_names.team_foreign_key'))
                        ->label(__('filament-shield::filament-shield.field.team'))
                        ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                        /** @phpstan-ignore-next-line */
                        ->default(Filament::getTenant()?->id)
                        ->options(fn (): Arrayable => Utils::getTenantModel() ? Utils::getTenantModel()::pluck('name', 'id') : collect())
                        ->hidden(fn (): bool => ! (RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled()))
                        ->dehydrated(fn (): bool => ! (RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled())),
                    RoleResource::getSelectAllFormComponent(),

                ])
                    ->columns([
                        'sm' => 2,
                        'lg' => 3,
                    ]),
                RoleResource::getShieldFormComponents(),
            ]);
    }
}
