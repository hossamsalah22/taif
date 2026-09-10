<?php

namespace App\Events;

use App\Http\Resources\SupportTicketReplyResource;
use App\Models\SupportTicketReply;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketReplyCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reply;

    /**
     * Create a new event instance.
     */
    public function __construct(SupportTicketReply $reply)
    {
        $this->reply = $reply;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support-ticket.'.$this->reply->support_ticket_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'reply.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Ensure the related user and admin models are loaded before broadcasting
        $this->reply->loadMissing(['user', 'admin']);

        // Return a simple array representing the reply
        return [
            'id' => $this->reply->id,
            'support_ticket_id' => $this->reply->support_ticket_id,
            'reply_text' => $this->reply->reply_text,
            'user_id' => $this->reply->user_id,
            'admin_id' => $this->reply->admin_id,
            'created_at' => $this->reply->created_at->toIso8601String(),
            // You can also use a Resource class if you have one, e.g.:
            // 'reply' => (new SupportTicketReplyResource($this->reply))->resolve(),
        ];
    }
}
