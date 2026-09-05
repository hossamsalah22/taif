<?php

namespace App\Livewire;

use App\Enums\SupportTicketStatusEnum;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Notifications\SupportTicketRepliedNotification;
use Filament\Notifications\Notification;
use Livewire\Component;

class SupportTicketChat extends Component
{
    public SupportTicket $ticket;

    public string $replyText = '';

    public function sendReply()
    {
        $this->validate([
            'replyText' => 'required|string|max:2000',
        ]);

        if ($this->ticket->status === SupportTicketStatusEnum::CLOSED) {
            Notification::make()->title(__('This ticket is closed and cannot be replied to.'))->danger()->send();

            return;
        }

        if (! $this->ticket->assigned_admin_id) {
            $this->ticket->update(['assigned_admin_id' => auth()->id()]);
        }

        SupportTicketReply::create([
            'support_ticket_id' => $this->ticket->id,
            'admin_id' => auth()->id(),
            'reply_text' => $this->replyText,
        ]);

        $this->ticket->update(['status' => SupportTicketStatusEnum::REPLIED]);

        if ($this->ticket->user) {
            $this->ticket->user->notify(new SupportTicketRepliedNotification($this->ticket));
        }

        $this->replyText = '';

        Notification::make()->title(__('Reply sent successfully to parent.'))->success()->send();

        $this->dispatch('reply-sent');
    }

    public function render()
    {
        return view('livewire.support-ticket-chat', [
            'replies' => $this->ticket->replies()->with(['admin', 'user'])->oldest()->get(),
        ]);
    }
}
