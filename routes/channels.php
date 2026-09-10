<?php

use App\Models\Admin;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('support-ticket.{id}', function ($user, $id) {
    // Both App\Models\User and App\Models\Admin can authenticate
    // We should check if the user is the owner of the ticket or an admin.

    // For admins: check if the authenticated user is an Admin
    if ($user instanceof Admin) {
        return true;
    }

    // For normal users: check if they own the ticket
    $ticket = SupportTicket::find($id);

    return $ticket && (int) $ticket->user_id === (int) $user->id;
}, ['guards' => ['user', 'admin']]);
