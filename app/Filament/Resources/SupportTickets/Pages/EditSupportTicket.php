<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('showConversation')
                ->label(__('Show Conversation'))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->modalHeading(__('Conversation'))
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalContent(fn (SupportTicket $record) => new HtmlString(Blade::render('@livewire("support-ticket-chat", ["ticket" => $record])', ['record' => $record]))),

            Action::make('closeTicket')
                ->label(__('Close Ticket'))
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('Close Support Ticket'))
                ->modalDescription(__('Are you sure you want to close this ticket? Future replies will be disabled.'))
                ->action(function (SupportTicket $record) {
                    $record->update(['status' => SupportTicketStatusEnum::CLOSED]);
                })
                ->visible(fn (SupportTicket $record) => $record->status !== SupportTicketStatusEnum::CLOSED)
                ->successNotificationTitle(__('Ticket status updated successfully.')),

            // DeleteAction::make(),
        ];
    }
}
