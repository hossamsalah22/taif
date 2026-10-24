<?php

namespace Tests\Feature;

use App\Events\SupportTicketReplyCreated;
use App\Livewire\SupportTicketChat;
use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SupportTicketAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(SupportTicketAttachment::DISK);
        Event::fake([SupportTicketReplyCreated::class]);
    }

    public function test_user_can_create_ticket_with_attachments(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'user')
            ->postJson('/api/user/support-tickets', [
                'title' => 'Payment problem',
                'description' => 'See the screenshot',
                'attachments' => [
                    UploadedFile::fake()->image('screenshot.png'),
                    UploadedFile::fake()->create('invoice.pdf', 200, 'application/pdf'),
                ],
            ])
            ->assertOk()
            ->assertJsonCount(2, 'data.attachments')
            ->assertJsonPath('data.attachments.0.name', 'screenshot.png')
            ->assertJsonPath('data.attachments.0.is_image', true)
            ->assertJsonPath('data.attachments.1.name', 'invoice.pdf')
            ->assertJsonPath('data.attachments.1.is_image', false);

        $ticket = SupportTicket::firstOrFail();
        $this->assertSame($ticket->id, $response->json('data.id'));
        $this->assertCount(2, $ticket->attachments);

        foreach ($ticket->attachments as $attachment) {
            $this->assertNull($attachment->support_ticket_reply_id);
            Storage::disk(SupportTicketAttachment::DISK)->assertExists($attachment->path);
        }
    }

    public function test_user_can_create_ticket_without_attachments(): void
    {
        $this->actingAs(User::factory()->create(), 'user')
            ->postJson('/api/user/support-tickets', [
                'title' => 'Plain ticket',
                'description' => 'No files here',
            ])
            ->assertOk()
            ->assertJsonCount(0, 'data.attachments');

        $this->assertDatabaseCount('support_ticket_attachments', 0);
    }

    public function test_ticket_attachment_with_disallowed_extension_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(), 'user')
            ->postJson('/api/user/support-tickets', [
                'title' => 'Bad file',
                'description' => 'Executable attached',
                'attachments' => [UploadedFile::fake()->create('virus.exe', 10)],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attachments.0');

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_attachment_larger_than_limit_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(), 'user')
            ->postJson('/api/user/support-tickets', [
                'title' => 'Huge file',
                'description' => 'Too large',
                'attachments' => [UploadedFile::fake()->create('big.pdf', SupportTicketAttachment::MAX_FILE_SIZE_KB + 1, 'application/pdf')],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attachments.0');
    }

    public function test_too_many_attachments_are_rejected(): void
    {
        $files = array_map(
            fn (int $i): UploadedFile => UploadedFile::fake()->image("photo{$i}.jpg"),
            range(1, SupportTicketAttachment::MAX_FILES + 1),
        );

        $this->actingAs(User::factory()->create(), 'user')
            ->postJson('/api/user/support-tickets', [
                'title' => 'Many files',
                'description' => 'Too many',
                'attachments' => $files,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attachments');
    }

    public function test_user_can_reply_with_attachments_only(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($ticket->user, 'user')
            ->postJson("/api/user/support-tickets/{$ticket->id}/reply", [
                'attachments' => [UploadedFile::fake()->image('photo.jpg')],
            ])
            ->assertOk()
            ->assertJsonPath('data.replies.0.reply_text', '')
            ->assertJsonCount(1, 'data.replies.0.attachments')
            ->assertJsonPath('data.replies.0.attachments.0.name', 'photo.jpg');

        $reply = SupportTicketReply::firstOrFail();
        $this->assertCount(1, $reply->attachments);
        $this->assertSame($ticket->id, $reply->attachments->first()->support_ticket_id);
        $this->assertCount(0, $ticket->attachments);

        Event::assertDispatched(SupportTicketReplyCreated::class, function (SupportTicketReplyCreated $event): bool {
            $attachments = $event->broadcastWith()['attachments'];

            return count($attachments) === 1
                && $attachments[0]['name'] === 'photo.jpg'
                && $attachments[0]['is_image'] === true
                && str_contains($attachments[0]['url'], 'support-tickets/');
        });
    }

    public function test_reply_requires_text_or_attachments(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($ticket->user, 'user')
            ->postJson("/api/user/support-tickets/{$ticket->id}/reply", [], ['Accept-Language' => 'ar'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reply_text' => 'اكتب ردًا أو أرفق ملفًا.']);

        Event::assertNotDispatched(SupportTicketReplyCreated::class);
    }

    public function test_reply_with_invalid_attachment_is_rejected(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($ticket->user, 'user')
            ->postJson("/api/user/support-tickets/{$ticket->id}/reply", [
                'reply_text' => 'Hello',
                'attachments' => [UploadedFile::fake()->create('script.php', 5)],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attachments.0');

        $this->assertDatabaseCount('support_ticket_replies', 0);
    }

    public function test_other_user_cannot_reply_with_attachments_to_someone_elses_ticket(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs(User::factory()->create(), 'user')
            ->postJson("/api/user/support-tickets/{$ticket->id}/reply", [
                'attachments' => [UploadedFile::fake()->image('photo.jpg')],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('support_ticket_attachments', 0);
    }

    public function test_show_returns_ticket_and_reply_attachments(): void
    {
        $ticket = SupportTicket::factory()->create();
        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'reply_text' => 'With file',
        ]);
        SupportTicketAttachment::factory()->for($ticket, 'ticket')->create(['original_name' => 'ticket.jpg']);
        SupportTicketAttachment::factory()->for($ticket, 'ticket')->create([
            'support_ticket_reply_id' => $reply->id,
            'original_name' => 'reply.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($ticket->user, 'user')
            ->getJson("/api/user/support-tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.attachments')
            ->assertJsonPath('data.attachments.0.name', 'ticket.jpg')
            ->assertJsonCount(1, 'data.replies.0.attachments')
            ->assertJsonPath('data.replies.0.attachments.0.name', 'reply.pdf')
            ->assertJsonPath('data.replies.0.attachments.0.is_image', false);
    }

    public function test_deleting_attachment_removes_file_from_disk(): void
    {
        $ticket = SupportTicket::factory()->create();
        $attachment = SupportTicketAttachment::storeFor(UploadedFile::fake()->image('photo.jpg'), $ticket);

        Storage::disk(SupportTicketAttachment::DISK)->assertExists($attachment->path);

        $attachment->delete();

        Storage::disk(SupportTicketAttachment::DISK)->assertMissing($attachment->path);
    }

    public function test_admin_can_reply_with_attachments_from_chat(): void
    {
        Notification::fake();

        $ticket = SupportTicket::factory()->create();
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'web');

        Livewire::test(SupportTicketChat::class, ['ticket' => $ticket])
            ->set('attachments', [UploadedFile::fake()->image('answer.png')])
            ->call('sendReply')
            ->assertHasNoErrors()
            ->assertSet('attachments', [])
            ->assertDispatched('reply-sent');

        $reply = SupportTicketReply::firstOrFail();
        $this->assertSame($admin->id, $reply->admin_id);
        $this->assertCount(1, $reply->attachments);
        $this->assertSame('answer.png', $reply->attachments->first()->original_name);
        Storage::disk(SupportTicketAttachment::DISK)->assertExists($reply->attachments->first()->path);
    }

    public function test_admin_chat_requires_text_or_attachments(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->createAdmin(), 'web');

        app()->setLocale('ar');

        Livewire::test(SupportTicketChat::class, ['ticket' => $ticket])
            ->call('sendReply')
            ->assertHasErrors(['replyText' => 'required_without'])
            ->assertSee('اكتب ردًا أو أرفق ملفًا.')
            ->assertDontSee('reply text');

        $this->assertDatabaseCount('support_ticket_replies', 0);
    }

    public function test_admin_chat_rejects_disallowed_attachment(): void
    {
        $ticket = SupportTicket::factory()->create();

        $this->actingAs($this->createAdmin(), 'web');

        Livewire::test(SupportTicketChat::class, ['ticket' => $ticket])
            ->set('replyText', 'Hello')
            ->set('attachments', [UploadedFile::fake()->create('virus.exe', 10)])
            ->call('sendReply')
            ->assertHasErrors('attachments.0');

        $this->assertDatabaseCount('support_ticket_replies', 0);
    }

    public function test_admin_chat_renders_existing_attachments(): void
    {
        $ticket = SupportTicket::factory()->create();
        SupportTicketAttachment::factory()->for($ticket, 'ticket')->create(['original_name' => 'original-issue.pdf', 'mime_type' => 'application/pdf']);

        $this->actingAs($this->createAdmin(), 'web');

        Livewire::test(SupportTicketChat::class, ['ticket' => $ticket])
            ->assertSee('original-issue.pdf');
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Support Admin',
            'email' => 'support@example.com',
            'phone' => '500000000',
            'password' => 'password',
        ]);
    }
}
