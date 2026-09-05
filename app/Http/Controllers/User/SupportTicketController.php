<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ReplySupportTicketRequest;
use App\Http\Requests\User\StoreSupportTicketRequest;
use App\Http\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Enums\SupportTicketStatusEnum;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = SupportTicket::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        if ($tickets->isEmpty()) {
            return $this->successResponse(__('No support tickets found linked to your profile account history.'), []);
        }

        return $this->successResponse(__('Tickets History List retrieved successfully.'), SupportTicketResource::collection($tickets));
    }

    /**
     * Store a newly created support ticket in storage.
     */
    public function store(StoreSupportTicketRequest $request)
    {
        $ticket = SupportTicket::create($request->validated());

        return $this->successResponse(__('Ticket created successfully'), SupportTicketResource::make($ticket));
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $supportTicket)
    {
        if ($supportTicket->user_id !== auth()->id()) {
            abort(403);
        }

        $supportTicket->load(['replies.admin', 'replies.user']);

        return $this->successResponse(__('Ticket details retrieved successfully.'), SupportTicketResource::make($supportTicket));
    }

    /**
     * Reply to a support ticket.
     */
    public function reply(ReplySupportTicketRequest $request, SupportTicket $supportTicket)
    {
        if ($supportTicket->status === SupportTicketStatusEnum::CLOSED) {
            return response()->json(['message' => __('This ticket is closed and cannot be replied to.')], 400);
        }

        SupportTicketReply::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => auth()->id(),
            'reply_text' => $request->validated('reply_text'),
        ]);

        $supportTicket->load(['replies.admin', 'replies.user']);

        return $this->successResponse(__('Reply sent successfully'), SupportTicketResource::make($supportTicket));
    }
}
