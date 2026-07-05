<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Notifications\AdminWelcomeNotification;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected string $plainPassword;

    protected function getRedirectUrl(): string
    {
        return AdminResource::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate random password
        $this->plainPassword = str()->random(10);

        // Hash password for saving
        $data['password'] = bcrypt($this->plainPassword);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->notify(new AdminWelcomeNotification($this->plainPassword));
    }
}
