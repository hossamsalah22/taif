<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\SupportTicketRepliedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArabicApiMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_message_is_in_arabic(): void
    {
        $user = User::factory()->create(['phone' => prepare_phone('512345678', 'SA')]);

        $this->actingAs($user, 'user')
            ->withHeader('Accept-Language', 'ar')
            ->putJson('/api/user/profile', [
                'name' => 'Ahmed Ali',
                'country_code' => 'SA',
                'phone' => '512345678',
                'email' => $user->email,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'تم تحديث الملف الشخصي بنجاح.');
    }

    public function test_notification_message_is_translated_to_the_reader_locale(): void
    {
        $user = User::factory()->create(['locale' => 'ar']);
        $ticket = SupportTicket::factory()->create(['user_id' => $user->id]);

        app()->setLocale('en');
        $user->notifyNow(new SupportTicketRepliedNotification($ticket), ['database']);

        $this->actingAs($user, 'user')
            ->getJson('/api/user/notifications')
            ->assertOk()
            ->assertJsonPath('data.notifications.0.data.message', "تم الرد على تذكرة الدعم الخاصة بك {$ticket->reference_number}.")
            ->assertJsonMissingPath('data.notifications.0.data.message_key');
    }

    public function test_old_notifications_without_a_key_are_translated(): void
    {
        $user = User::factory()->create(['locale' => 'ar']);
        $user->notifications()->create([
            'id' => fake()->uuid(),
            'type' => 'App\\Notifications\\ReportPublishedNotification',
            'data' => ['message' => "Your child's screening assessment report is ready. Tap to view the official specialist analysis."],
        ]);

        $this->actingAs($user, 'user')
            ->getJson('/api/user/notifications')
            ->assertOk()
            ->assertJsonPath('data.notifications.0.data.message', 'تقرير التقييم الأولي لطفلك جاهز. اضغط لعرض تحليل الأخصائي المعتمد.');
    }
}
