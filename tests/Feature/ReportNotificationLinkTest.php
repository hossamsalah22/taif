<?php

namespace Tests\Feature;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Enums\AutismLevelEnum;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Notifications\ReportPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportNotificationLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_notification_links_to_the_published_report(): void
    {
        $submission = $this->createSubmission();
        $parent = $submission->child->parent;

        $submission->publish();

        $reportUrl = route('api.reports.show', ['child' => $submission->child_id, 'submission' => $submission->id]);

        $notification = $this->actingAs($parent, 'user')
            ->getJson('/api/user/notifications')
            ->assertOk()
            ->assertJsonPath('data.notifications.0.type', 'ReportPublishedNotification')
            ->assertJsonPath('data.notifications.0.data.submission_id', $submission->id)
            ->assertJsonPath('data.notifications.0.data.child_id', $submission->child_id)
            ->assertJsonPath('data.notifications.0.data.report_url', $reportUrl);

        $this->getJson($reportUrl)
            ->assertOk()
            ->assertJsonPath('report.submission_id', $submission->id);
    }

    public function test_older_report_notifications_get_the_report_link(): void
    {
        $submission = $this->createSubmission();
        $submission->update(['status' => AssessmentSubmissionStatusEnum::PUBLISHED]);
        $parent = $submission->child->parent;

        $parent->notifications()->create([
            'id' => fake()->uuid(),
            'type' => ReportPublishedNotification::class,
            'data' => ['submission_id' => $submission->id, 'message' => ReportPublishedNotification::MESSAGE],
        ]);

        $this->actingAs($parent, 'user')
            ->getJson('/api/user/notifications')
            ->assertOk()
            ->assertJsonPath('data.notifications.0.data.child_id', $submission->child_id)
            ->assertJsonPath(
                'data.notifications.0.data.report_url',
                route('api.reports.show', ['child' => $submission->child_id, 'submission' => $submission->id]),
            );
    }

    public function test_report_notification_for_a_deleted_submission_has_no_link(): void
    {
        $submission = $this->createSubmission();
        $parent = $submission->child->parent;

        $parent->notifications()->create([
            'id' => fake()->uuid(),
            'type' => ReportPublishedNotification::class,
            'data' => ['submission_id' => $submission->id + 1000, 'message' => ReportPublishedNotification::MESSAGE],
        ]);

        $this->actingAs($parent, 'user')
            ->getJson('/api/user/notifications')
            ->assertOk()
            ->assertJsonMissingPath('data.notifications.0.data.report_url');
    }

    private function createSubmission(): AssessmentSubmission
    {
        $assessment = Assessment::firstOrCreate(
            ['autism_level' => AutismLevelEnum::MILD->value],
            ['title' => ['en' => 'Screening', 'ar' => 'فحص']],
        );

        return AssessmentSubmission::create([
            'child_id' => Child::factory()->create()->id,
            'assessment_id' => $assessment->id,
            'status' => 'reviewed_with_report',
            'performance_accuracy' => 80,
        ]);
    }
}
