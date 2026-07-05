<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public Collection $permissions;

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->name === 'Super Admin') {
            Notification::make()
                ->title(__('Error'))
                ->body(__('cannot_edit_super_admin_role'))
                ->danger()
                ->send();

            $this->redirect(RoleResource::getUrl('index'));
        }
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (Role $record): bool => $record->name !== 'Super Admin')->action(function (Role $record) {
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
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissions = collect($data)
            ->filter(function ($permission, $key) {
                return ! in_array($key, ['name', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()]);
            })
            ->values()
            ->flatten()
            ->unique();

        if (Arr::has($data, Utils::getTenantModelForeignKey())) {
            return Arr::only($data, ['name', 'guard_name', Utils::getTenantModelForeignKey()]);
        }

        return Arr::only($data, ['name', 'guard_name']);
    }

    protected function afterSave(): void
    {
        $permissionModels = collect();
        $this->permissions->each(function ($permission) use ($permissionModels) {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->data['guard_name'],
            ]));
        });

        $this->record->syncPermissions($permissionModels);
    }
}
