<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Spatie\Permission\Models\Role;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (Role $record): bool => $record->name !== 'Super Admin')
                ->action(function (Role $record) {
                    $record->name !== 'Super Admin' ? $this->redirect(RoleResource::getUrl('edit', ['record' => $record])) : $this->redirect(RoleResource::getUrl('index'));
                }),
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
