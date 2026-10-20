<?php

namespace Tests\Feature;

use App\Events\SupportTicketReplyCreated;
use App\Livewire\SupportTicketChat;
use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class SupportTicketBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The Socket.IO server relies on the redis broadcaster's channel authorization.
        // Channels are registered on the default driver at boot, so re-register them on redis.
        config(['broadcasting.default' => 'redis']);
        require base_path('routes/channels.php');
    }

    public function test_ticket_owner_can_authorize_private_ticket_channel_with_bearer_token(): void
    {
        $ticket = SupportTicket::factory()->create();
        $token = $ticket->user->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->postJson('/broadcasting/auth', ['channel_name' => 'private-support-ticket.'.$ticket->id])
            ->assertOk();
    }

    public function test_other_user_cannot_authorize_someone_elses_ticket_channel(): void
    {
        $ticket = SupportTicket::factory()->create();
        $token = User::factory()->create()->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->postJson('/broadcasting/auth', ['channel_name' => 'private-support-ticket.'.$ticket->id])
            ->assertForbidden();
    }

    public function test_guest_cannot_authorize_ticket_channel(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->postJson('/broadcasting/auth', ['channel_name' => 'private-support-ticket.'.$ticket->id])
            ->assertForbidden();
    }

    public function test_admin_can_authorize_ticket_channel_with_panel_session(): void
    {
        $ticket = SupportTicket::factory()->create();
        $admin = Admin::create([
            'name' => 'Support Admin',
            'email' => 'support@example.com',
            'phone' => '500000000',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'web')
            ->postJson('/broadcasting/auth', ['channel_name' => 'private-support-ticket.'.$ticket->id])
            ->assertOk();
    }

    public function test_admin_chat_listens_for_reply_broadcasts_on_ticket_channel(): void
    {
        $ticket = SupportTicket::factory()->create();

        Livewire::test(SupportTicketChat::class, ['ticket' => $ticket])
            ->dispatch("echo-private:support-ticket.{$ticket->id},.reply.created")
            ->assertDispatched('reply-sent');
    }

    public function test_replying_from_api_dispatches_broadcast_event_on_ticket_channel(): void
    {
        Event::fake([SupportTicketReplyCreated::class]);

        $ticket = SupportTicket::factory()->create();

        $this->actingAs($ticket->user, 'user')
            ->postJson("/api/user/support-tickets/{$ticket->id}/reply", ['reply_text' => 'Hello'])
            ->assertOk();

        Event::assertDispatched(SupportTicketReplyCreated::class, function (SupportTicketReplyCreated $event) use ($ticket): bool {
            return $event->broadcastAs() === 'reply.created'
                && $event->broadcastOn()[0]->name === 'private-support-ticket.'.$ticket->id
                && $event->broadcastWith()['reply_text'] === 'Hello';
        });
    }

    public function test_reply_event_payload_contains_expected_keys(): void
    {
        $ticket = SupportTicket::factory()->create();
        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'reply_text' => 'Hi there',
        ]);

        $payload = (new SupportTicketReplyCreated($reply))->broadcastWith();

        $this->assertSame(
            ['id', 'support_ticket_id', 'reply_text', 'user_id', 'admin_id', 'created_at'],
            array_keys($payload),
        );
    }
}
