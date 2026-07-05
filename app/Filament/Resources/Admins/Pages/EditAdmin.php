<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Notifications\AdminEmailUpdatedNotification;
use DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! auth('web')->user()->hasRole('Super Admin') && $this->record->hasRole('Super Admin')) {
            Notification::make()
                ->warning()
                ->title(__('limited_edit_warning'))
                ->body(__('you_cannot_modify_critical_fields'))
                ->persistent()
                ->send();
            $this->redirect(AdminResource::getUrl('index'));
        }
    }

    protected function getRedirectUrl(): string
    {
        return AdminResource::getUrl('index');
    }

    protected function afterSave()
    {
        if ($this->record->wasChanged('email')) {
            $this->record->notify(new AdminEmailUpdatedNotification);
            DB::table('sessions')
                ->where('user_id', $this->record->id)
                ->delete();
        }
    }
}
