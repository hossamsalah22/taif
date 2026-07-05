<?php

namespace App\Filament\Resources\Roles\Tables;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Str;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->defaultSort('name', 'asc')
            ->columns([
                // id
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->searchable(),
                TextColumn::make('name')
                    ->weight('font-medium')
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->searchable(),
                // TextColumn::make('guard_name')
                //     ->badge()
                //     ->color('warning')
                //     ->label(__('filament-shield::filament-shield.column.guard_name')),
                TextColumn::make('users_count')
                    ->badge()
                    ->label(__('admins'))
                    ->counts('users')
                    ->colors(['success']),
                // TextColumn::make('team.name')
                //     ->default('Global')
                //     ->badge()
                //     ->color(fn (mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                //     ->label(__('filament-shield::filament-shield.column.team'))
                //     ->searchable()
                //     ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                // TextColumn::make('permissions_count')
                //     ->badge()
                //     ->label(__('filament-shield::filament-shield.column.permissions'))
                //     ->counts('permissions')
                //     ->colors(['success']),
                // TextColumn::make('updated_at')
                //     ->label(__('filament-shield::filament-shield.column.updated_at'))
                //     ->dateTime(),
            ])

            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(
                        fn (Role $record): bool => $record->name === 'Super Admin'
                    ),
                DeleteAction::make()
                    ->visible(fn (Role $record): bool => $record->name !== 'Super Admin')
                    ->action(function (Role $record) {
                        if ($record->name === 'Super Admin') {
                            Notification::make()
                                ->title(__('Error'))
                                ->body(__('cannot_delete_super_admin_role'))
                                ->danger()
                                ->send();

                            return;
                        }
                        if ($record->users()->count()) {
                            Notification::make()
                                ->title(__('Error'))
                                ->body(__('you_cannot_delete_role_assigned_to_users'))
                                ->danger()
                                ->send();

                            return;
                        }
                        $record->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, Collection $records) {
                            $isSuperAdmin = $records->some(
                                fn (Role $role) => $role->name === 'Super Admin'
                            );

                            $hasUsers = $records->some(
                                fn (Role $role) => $role->users()->count() > 0
                            );

                            if ($isSuperAdmin) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('Error'))
                                    ->body(__('cannot_delete_super_admin_role'))
                                    ->send();
                                $action->cancel();
                            }

                            if ($hasUsers) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('Error'))
                                    ->body(__('you_cannot_delete_role_assigned_to_users'))
                                    ->send();
                                $action->cancel();
                            }
                        }),

                ]),
            ]);
    }
}
