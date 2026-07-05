<?php

namespace App\Filament\Resources\Admins\Tables;

use App\Models\Admin;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class AdminsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('name')
                    ->label(__('name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label(__('roles'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_login_at')
                    ->label(__('last_login_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->onIcon(Heroicon::OutlinedCheckCircle)
                    ->offIcon(Heroicon::OutlinedXCircle)
                    ->label(__('is_active'))
                    ->afterStateUpdated(function (Admin $record, bool $state): void {
                        $state ? Notification::make()
                            ->title(__('activated'))
                            ->success()
                            ->send() :
                            Notification::make()
                                ->title(__('deactivated'))
                                ->danger()
                                ->send();
                    })
                    ->disabled(fn (Admin $record): bool => $record->id === auth('web')->id() || ! auth('web')->user()->can('activate_user') || $record->hasRole('Super Admin')),
                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->hidden(
                        fn (Admin $record): bool => $record->id === auth('web')->id() ||
                            (! auth('web')->user()->hasRole('Super Admin') && $record->hasRole('Super Admin')) || $record->trashed()
                    ),
                DeleteAction::make()
                    ->hidden(
                        fn (Admin $record): bool => $record->id === auth('web')->id() ||
                            $record->hasRole('Super Admin') || $record->trashed()
                    )
                    ->before(function (Admin $record, DeleteAction $action) {
                        if ($record->id === auth('web')->id()) {
                            Notification::make()
                                ->danger()
                                ->title(__('cannot_delete_yourself'))
                                ->send();
                            $action->cancel();
                        }

                        if ($record->hasRole('Super Admin')) {
                            Notification::make()
                                ->danger()
                                ->title(__('cannot_delete_super_admin'))
                                ->send();
                            $action->cancel();
                        }
                    }),
                RestoreAction::make(),
                ForceDeleteAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, Collection $records) {
                            $currentUserId = auth('web')->id();

                            if ($records->contains('id', $currentUserId)) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('cannot_delete_yourself'))
                                    ->send();
                                $action->cancel();

                                return;
                            }

                            $hasSuperAdmin = $records->some(
                                fn (Admin $admin) => $admin->hasRole('Super Admin')
                            );

                            if ($hasSuperAdmin) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('cannot_delete_super_admin'))
                                    ->send();
                                $action->cancel();
                            }
                        }),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
