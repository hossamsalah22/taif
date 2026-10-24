<?php

namespace Tests\Feature;

use App\Enums\AssessmentStatusEnum;
use App\Enums\AutismLevelEnum;
use App\Filament\Resources\Assessments\AssessmentResource;
use App\Filament\Resources\Assessments\Pages\ListAssessments;
use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Models\Admin;
use App\Models\Assessment;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAssessmentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    public function test_view_opens_the_assessment_on_its_own_page(): void
    {
        $assessment = $this->createAssessment(AssessmentStatusEnum::ACTIVE);
        $viewUrl = AssessmentResource::getUrl('view', ['record' => $assessment]);

        Livewire::test(ListAssessments::class)
            ->assertActionHasUrl(TestAction::make('view')->table($assessment), $viewUrl);

        app()->setLocale('ar');

        $this->get($viewUrl)
            ->assertOk()
            ->assertSee('عرض التقييم');
    }

    public function test_only_a_draft_can_be_edited_from_its_page(): void
    {
        $active = $this->createAssessment(AssessmentStatusEnum::ACTIVE);
        $draft = $this->createAssessment(AssessmentStatusEnum::DRAFT);

        Livewire::test(ViewAssessment::class, ['record' => $active->id])
            ->assertActionHidden('edit');

        Livewire::test(ViewAssessment::class, ['record' => $draft->id])
            ->assertActionVisible('edit')
            ->assertActionHasUrl('edit', AssessmentResource::getUrl('edit', ['record' => $draft]));
    }

    public function test_continue_editing_links_to_the_draft_whatever_page_the_list_was_on(): void
    {
        $draft = $this->createAssessment(AssessmentStatusEnum::DRAFT);

        Livewire::withHeaders(['referer' => AssessmentResource::getUrl('index', ['page' => 2])])
            ->test(ListAssessments::class)
            ->assertActionHasUrl(TestAction::make('edit_draft')->table($draft), AssessmentResource::getUrl('edit', ['record' => $draft]));
    }

    private function createAssessment(AssessmentStatusEnum $status): Assessment
    {
        return Assessment::create([
            'autism_level' => AutismLevelEnum::MILD->value,
            'title' => ['en' => 'Screening', 'ar' => 'فحص'],
            'status' => $status->value,
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
