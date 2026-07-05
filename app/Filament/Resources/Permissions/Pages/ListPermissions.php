<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_permissions')
                ->label(__('sync_permissions'))
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    Artisan::call('permissions:sync');

                    Notification::make()
                        ->success()
                        ->title(__('permissions_synchronized_successfully'))
                        ->send();
                })->requiresConfirmation(),
        ];
    }
}
