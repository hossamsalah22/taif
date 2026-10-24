<?php

namespace App\Livewire;

use App\Enums\SupportTicketStatusEnum;
use App\Events\SupportTicketReplyCreated;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketReply;
use App\Notifications\SupportTicketRepliedNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class SupportTicketChat extends Component
{
    use WithFileUploads;

    public SupportTicket $ticket;

    public string $replyText = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

    public function sendReply()
    {
        $this->validate(
            [
                'replyText' => ['required_without:attachments', 'nullable', 'string', 'max:2000'],
                ...SupportTicketAttachment::validationRules(),
            ],
            ['replyText.required_without' => __('Write a reply or attach a file.')],
            ['replyText' => __('Reply'), 'attachments' => __('Attachments'), 'attachments.*' => __('Attachments')],
        );

        if ($this->ticket->status === SupportTicketStatusEnum::CLOSED) {
            Notification::make()->title(__('This ticket is closed and cannot be replied to.'))->danger()->send();

            return;
        }

        if (! $this->ticket->assigned_admin_id) {
            $this->ticket->update(['assigned_admin_id' => auth()->id()]);
        }

        $reply = DB::transaction(function (): SupportTicketReply {
            $reply = SupportTicketReply::create([
                'support_ticket_id' => $this->ticket->id,
                'admin_id' => auth()->id(),
                'reply_text' => $this->replyText,
            ]);

            foreach ($this->attachments as $file) {
                SupportTicketAttachment::storeFor($file, $this->ticket, $reply);
            }

            return $reply;
        });

        event(new SupportTicketReplyCreated($reply));

        $this->ticket->update(['status' => SupportTicketStatusEnum::REPLIED]);

        if ($this->ticket->user) {
            $this->ticket->user->notify(new SupportTicketRepliedNotification($this->ticket));
        }

        $this->replyText = '';
        $this->attachments = [];

        Notification::make()->title(__('Reply sent successfully to parent.'))->success()->send();

        $this->dispatch('reply-sent');
    }

    #[On('echo-private:support-ticket.{ticket.id},.reply.created')]
    public function refreshChat()
    {
        // This method will be triggered when a new reply is broadcasted.
        // Livewire will automatically re-render the component, then scroll to the newest reply.
        $this->dispatch('reply-sent');
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function render()
    {
        return view('livewire.support-ticket-chat', [
            'ticketAttachments' => $this->ticket->attachments()->get(),
            'replies' => $this->ticket->replies()->with(['admin', 'user', 'attachments'])->oldest()->get(),
        ]);
    }
}
