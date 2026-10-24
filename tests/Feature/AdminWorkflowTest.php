<?php

namespace Tests\Feature;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Enums\AutismLevelEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Filament\Resources\AssessmentSubmissions\Pages\EditAssessmentSubmission;
use App\Filament\Resources\AssessmentSubmissions\Pages\ListAssessmentSubmissions;
use App\Filament\Resources\AssessmentSubmissions\Pages\ViewAssessmentSubmission;
use App\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Admin;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\ReportPublishedNotification;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Assessment review workflow, ticket handling and the free subscription action in the admin panel.
 */
class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-21 10:00:00');
        $this->admin = $this->createSuperAdmin();
        $this->actingAs($this->admin, 'web');
    }

    public function test_submission_status_is_an_enum_and_legacy_completed_rows_wait_for_review(): void
    {
        $pending = $this->createSubmission('pending_review');
        $this->createSubmission('completed');
        $this->createSubmission('published');

        $this->assertSame(AssessmentSubmissionStatusEnum::PENDING_REVIEW, $pending->refresh()->status);
        $this->assertSame('2', AssessmentSubmissionResource::getNavigationBadge());
    }

    public function test_submissions_open_on_the_waiting_tab_and_each_step_has_a_tab(): void
    {
        $waiting = $this->createSubmission('pending_review');
        $reviewed = $this->createSubmission('reviewed_with_report');
        $published = $this->createSubmission('published');

        Livewire::test(ListAssessmentSubmissions::class)
            ->assertSet('activeTab', 'waiting')
            ->assertCanSeeTableRecords([$waiting])
            ->assertCanNotSeeTableRecords([$reviewed, $published])
            ->set('activeTab', 'reviewed')
            ->assertCanSeeTableRecords([$reviewed])
            ->assertCanNotSeeTableRecords([$waiting, $published])
            ->set('activeTab', 'published')
            ->assertCanSeeTableRecords([$published])
            ->set('activeTab', 'all')
            ->assertCanSeeTableRecords([$waiting, $reviewed, $published]);
    }

    public function test_publishing_a_report_notifies_the_parent(): void
    {
        Notification::fake();
        $submission = $this->createSubmission('reviewed_with_report');

        Livewire::test(ListAssessmentSubmissions::class, ['activeTab' => 'reviewed'])
            ->callAction(TestAction::make('publish')->table($submission))
            ->assertNotified(__('Report Published Successfully'));

        $submission->refresh();
        $this->assertSame(AssessmentSubmissionStatusEnum::PUBLISHED, $submission->status);
        $this->assertNotNull($submission->report_published_at);
        Notification::assertSentTo($submission->child->parent, ReportPublishedNotification::class);
    }

    public function test_publishing_from_the_view_page_notifies_the_parent(): void
    {
        $submission = $this->createSubmission('reviewed_with_report');

        Livewire::test(ViewAssessmentSubmission::class, ['record' => $submission->id])
            ->assertActionHasLabel('edit', __('Evaluate'))
            ->callAction('publish')
            ->assertHasNoErrors()
            ->assertNotified(__('Report Published Successfully'));

        $this->assertSame(AssessmentSubmissionStatusEnum::PUBLISHED, $submission->refresh()->status);
    }

    public function test_publishing_for_a_parent_who_deleted_their_account_does_not_fail(): void
    {
        Notification::fake();
        $fromView = $this->createSubmission('reviewed_with_report');
        $fromList = $this->createSubmission('reviewed_with_report');
        $fromView->child->parent->delete();
        $fromList->child->parent->delete();

        Livewire::test(ViewAssessmentSubmission::class, ['record' => $fromView->id])
            ->callAction('publish')
            ->assertNotified(__('Report Published Successfully'));

        Livewire::test(ListAssessmentSubmissions::class, ['activeTab' => 'reviewed'])
            ->callAction(TestAction::make('publish')->table($fromList))
            ->assertNotified(__('Report Published Successfully'));

        $this->assertSame(AssessmentSubmissionStatusEnum::PUBLISHED, $fromView->refresh()->status);
        $this->assertSame(AssessmentSubmissionStatusEnum::PUBLISHED, $fromList->refresh()->status);
        Notification::assertNothingSent();
    }

    public function test_report_can_be_exported_as_pdf(): void
    {
        $submission = $this->createSubmission('published');
        $submission->update(['specialist_notes' => '<p>ملاحظات الأخصائي على أداء الطفل.</p>', 'strengths' => 'التواصل البصري']);

        Livewire::test(ListAssessmentSubmissions::class, ['activeTab' => 'all'])
            ->callAction(TestAction::make('export_pdf')->table($submission))
            ->assertFileDownloaded("report-{$submission->id}.pdf");
    }

    public function test_parent_can_download_the_published_report_as_pdf(): void
    {
        $submission = $this->createSubmission('published');
        $submission->update(['specialist_notes' => '<p>ملاحظات الأخصائي على أداء الطفل.</p>']);
        $child = $submission->child;

        $response = $this->actingAs($child->parent, 'user')
            ->get(route('api.reports.download', ['child' => $child, 'submission' => $submission]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_re_evaluating_a_published_report_keeps_it_published(): void
    {
        $published = $this->createSubmission('published');
        $waiting = $this->createSubmission('pending_review');

        Livewire::test(ListAssessmentSubmissions::class, ['activeTab' => 'all'])
            ->callAction(TestAction::make('edit')->table($published), ['specialist_notes' => '<p>Updated notes after a follow-up session.</p>'])
            ->assertHasNoFormErrors()
            ->callAction(TestAction::make('edit')->table($waiting), ['specialist_notes' => '<p>First review of the screening answers.</p>'])
            ->assertHasNoFormErrors();

        $this->assertSame(AssessmentSubmissionStatusEnum::PUBLISHED, $published->refresh()->status);
        $this->assertSame(AssessmentSubmissionStatusEnum::REVIEWED, $waiting->refresh()->status);
    }

    public function test_saving_the_edit_page_returns_to_the_edited_submission(): void
    {
        $submission = $this->createSubmission('pending_review');

        $this->assertStringEndsWith(
            "/assessment-submissions/{$submission->id}/evaluate",
            AssessmentSubmissionResource::getUrl('edit', ['record' => $submission]),
        );

        Livewire::test(EditAssessmentSubmission::class, ['record' => $submission->id])
            ->assertSeeHtmlInOrder(['fi-header-heading', __('Evaluate'), '</h1>'])
            ->fillForm(['specialist_notes' => '<p>Notes from the edit page.</p>'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(AssessmentSubmissionResource::getUrl('view', ['record' => $submission]));

        $submission->refresh();
        $this->assertSame('<p>Notes from the edit page.</p>', $submission->specialist_notes);
        $this->assertSame(AssessmentSubmissionStatusEnum::REVIEWED, $submission->status);
    }

    public function test_editing_a_published_submission_on_the_edit_page_keeps_it_published(): void
    {
        $published = $this->createSubmission('published');

        Livewire::test(EditAssessmentSubmission::class, ['record' => $published->id])
            ->fillForm(['specialist_notes' => '<p>Follow-up notes after the next session.</p>'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(AssessmentSubmissionStatusEnum::PUBLISHED, $published->refresh()->status);
    }

    public function test_parent_app_still_receives_the_status_as_a_string(): void
    {
        $submission = $this->createSubmission('published');

        $this->actingAs($submission->child->parent, 'user')
            ->getJson("/api/user/children/{$submission->child->id}/status")
            ->assertOk()
            ->assertJsonFragment(['has_report' => true, 'assessment_status' => 'published']);
    }

    public function test_tickets_open_on_not_closed_and_each_status_has_a_tab(): void
    {
        $open = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::OPEN]);
        $replied = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::REPLIED]);
        $closed = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::CLOSED]);

        Livewire::test(ListSupportTickets::class)
            ->assertSet('activeTab', 'active')
            ->assertCanSeeTableRecords([$open, $replied])
            ->assertCanNotSeeTableRecords([$closed])
            ->set('activeTab', 'closed')
            ->assertCanSeeTableRecords([$closed])
            ->assertCanNotSeeTableRecords([$open, $replied])
            ->set('activeTab', 'all')
            ->assertCanSeeTableRecords([$open, $replied, $closed]);
    }

    public function test_assigning_a_new_ticket_to_yourself_marks_it_in_progress(): void
    {
        $ticket = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::OPEN]);

        Livewire::test(ListSupportTickets::class)
            ->callAction(TestAction::make('assignToMe')->table($ticket))
            ->assertNotified();

        $ticket->refresh();
        $this->assertSame($this->admin->id, $ticket->assigned_admin_id);
        $this->assertSame(SupportTicketStatusEnum::IN_PROGRESS, $ticket->status);

        Livewire::test(ListSupportTickets::class)
            ->assertActionHidden(TestAction::make('assignToMe')->table($ticket));
    }

    public function test_status_can_be_changed_from_the_list_but_not_on_closed_tickets(): void
    {
        $ticket = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::IN_PROGRESS]);
        $closed = SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::CLOSED]);

        Livewire::test(ListSupportTickets::class, ['activeTab' => 'all'])
            ->callAction(TestAction::make('changeStatus')->table($ticket), ['status' => SupportTicketStatusEnum::REPLIED->value])
            ->assertHasNoFormErrors()
            ->assertActionHidden(TestAction::make('changeStatus')->table($closed))
            ->assertActionHidden(TestAction::make('assignToMe')->table($closed));

        $this->assertSame(SupportTicketStatusEnum::REPLIED, $ticket->refresh()->status);
    }

    public function test_free_subscription_gives_running_access_and_a_translated_message(): void
    {
        $parent = User::factory()->create();
        Child::factory()->for($parent, 'parent')->create();

        Livewire::test(ListUsers::class)
            ->callAction(TestAction::make('free_subscription')->table($parent), ['duration_days' => 30])
            ->assertNotified(__('Free Subscription Granted'));

        $subscription = $parent->subscriptions()->sole();
        $this->assertSame(SubscriptionStatusEnum::ACTIVE, $subscription->status);
        $this->assertTrue((bool) $subscription->is_free);
        $this->assertSame('2026-11-20', Carbon::parse($subscription->expiry_date)->format('Y-m-d'));
        $this->assertSame(1, $parent->subscriptions()->runningNow()->count());
    }

    private function createSubmission(string $status): AssessmentSubmission
    {
        $assessment = Assessment::firstOrCreate(
            ['autism_level' => AutismLevelEnum::MILD->value],
            ['title' => ['en' => 'Screening', 'ar' => 'فحص']],
        );

        return AssessmentSubmission::create([
            'child_id' => Child::factory()->create()->id,
            'assessment_id' => $assessment->id,
            'status' => $status,
            'performance_accuracy' => 80,
        ]);
    }

    private function createSuperAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'phone' => '500000001',
            'password' => 'password',
            'is_active' => true,
        ]);

        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));

        return $admin;
    }
}
