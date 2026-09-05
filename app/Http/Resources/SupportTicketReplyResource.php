<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reply_text' => $this->reply_text,
            'is_admin' => $this->admin_id !== null,
            'sender_name' => $this->admin_id ? ($this->admin->name ?? 'System') : ($this->user->name ?? 'Parent'),
            'created_at' => formatDate($this->created_at),
        ];
    }
}
