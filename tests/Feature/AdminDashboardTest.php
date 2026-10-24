<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManageGeneral;
use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\SubscriptionPackages\Pages\CreateSubscriptionPackage;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Filament\Widgets\DashboardKpiOverview;
use App\Filament\Widgets\NeedsAttentionOverview;
use App\Filament\Widgets\PerformanceLineChart;
use App\Filament\Widgets\RevenueBreakdownOverview;
use App\Filament\Widgets\SkillMappingPieChart;
use App\Models\Admin;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-20 12:00:00');

        $settings = app(GeneralSettings::class);
        $settings->tax = 15;
        $settings->commission = 15;
        $settings->save();
    }

    public function test_kpis_split_revenue_in_the_selected_range_into_tax_commission_and_provider_share(): void
    {
        $this->createSubscription(115, '2026-10-05');
        $this->createSubscription(230, '2026-10-15');
        $this->createSubscription(1000, '2026-08-01'); // outside the range

        $kpis = AnalyticsService::getDashboardKpis(Carbon::parse('2026-10-01'), Carbon::parse('2026-10-20'));

        $this->assertSame('2026-10-01 - 2026-10-20', $kpis['period']);
        $this->assertSame(345.0, $kpis['total_revenue_sar']);
        $this->assertSame(45.0, $kpis['revenue_tax_sar']);
        $this->assertSame(300.0, $kpis['revenue_net_sar']);
        $this->assertSame(45.0, $kpis['platform_commission_sar']);
        $this->assertSame(255.0, $kpis['provider_share_sar']);
    }

    public function test_kpis_include_both_ends_of_the_range(): void
    {
        $this->createSubscription(115, '2026-10-01 00:00:00');
        $this->createSubscription(115, '2026-10-20 23:59:59');

        $kpis = AnalyticsService::getDashboardKpis(Carbon::parse('2026-10-01'), Carbon::parse('2026-10-20'));

        $this->assertSame(230.0, $kpis['total_revenue_sar']);
    }

    public function test_dashboard_page_renders_for_super_admin(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        $this->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee(__('Start Date'))
            ->assertSee(__('End Date'));
    }

    public function test_revenue_widget_explains_the_split_for_the_filtered_range(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');
        $this->createSubscription(115, '2026-10-10');
        $this->createSubscription(500, '2026-09-01'); // outside the range

        Livewire::test(RevenueBreakdownOverview::class, ['pageFilters' => ['startDate' => '2026-10-01', 'endDate' => '2026-10-20']])
            ->assertSee('115.00')
            ->assertSee('15.00')
            ->assertSee('85.00')
            ->assertDontSee('615.00')
            ->assertSee(__('Revenue & how it is split'));
    }

    public function test_other_dashboard_widgets_render_with_and_without_data(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');
        $filters = ['pageFilters' => ['startDate' => '2026-01-01', 'endDate' => '2026-10-20']];

        Livewire::test(DashboardKpiOverview::class, $filters)->assertOk()->assertSee(__('New Parents'));
        Livewire::test(PerformanceLineChart::class, $filters)->assertOk();
        Livewire::test(SkillMappingPieChart::class, $filters)->assertOk()->assertSee(__('No data available for the selected period.'));

        $this->createSubscription(115, '2026-10-10');

        Livewire::test(SkillMappingPieChart::class, $filters)->assertOk()->assertDontSee(__('No data available for the selected period.'));
    }

    public function test_settings_page_saves_tax_and_commission_but_not_the_example_price(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(ManageGeneral::class)
            ->fillForm([
                'tax' => 10,
                'commission' => 20,
                'example_price' => 220,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(GeneralSettings::class)->refresh();
        $this->assertEquals(10, $settings->tax);
        $this->assertEquals(20, $settings->commission);
        $this->assertArrayNotHasKey('example_price', $settings->toArray());
    }

    public function test_settings_page_previews_the_price_split(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(ManageGeneral::class)
            ->fillForm(['example_price' => 230])
            ->assertSee('200.00')
            ->assertSee('30.00')
            ->assertSee('170.00');
    }

    public function test_settings_page_rejects_rates_over_100(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(ManageGeneral::class)
            ->fillForm(['tax' => 150, 'commission' => -1])
            ->call('save')
            ->assertHasFormErrors(['tax', 'commission']);
    }

    public function test_package_price_field_shows_the_tax_and_commission_split(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(CreateSubscriptionPackage::class)
            ->fillForm(['price' => 230])
            ->assertSee('200.00')
            ->assertSee('30.00')
            ->assertSee('170.00');
    }

    public function test_previous_period_has_the_same_length_and_ends_the_day_before(): void
    {
        [$start, $end] = AnalyticsService::previousPeriod(Carbon::parse('2026-10-11'), Carbon::parse('2026-10-20 23:59:59'));

        $this->assertSame('2026-10-01 00:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-10 23:59:59', $end->format('Y-m-d H:i:s'));
    }

    public function test_kpi_cards_compare_with_the_previous_period(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');
        $parent = User::factory()->create(['created_at' => '2026-10-15']);
        User::factory()->create(['created_at' => '2026-10-15']);
        User::factory()->create(['created_at' => '2026-10-05']);
        Child::factory()->for($parent, 'parent')->create(['created_at' => '2026-10-16']);

        Livewire::test(DashboardKpiOverview::class, ['pageFilters' => ['startDate' => '2026-10-11', 'endDate' => '2026-10-20']])
            ->assertSee(__(':change% vs the previous :days days', ['change' => '+100', 'days' => 10]))
            ->assertSee(__('Up from none in the previous :days days', ['days' => 10]))
            ->assertSee(SubscriptionResource::getUrl('index', ['filters' => ['running_now' => ['isActive' => true]]]));
    }

    public function test_revenue_card_compares_with_the_previous_period(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');
        $this->createSubscription(100, '2026-10-15');
        $this->createSubscription(200, '2026-10-05');

        Livewire::test(RevenueBreakdownOverview::class, ['pageFilters' => ['startDate' => '2026-10-11', 'endDate' => '2026-10-20']])
            ->assertSee(__(':change% vs the previous :days days', ['change' => '-50', 'days' => 10]));
    }

    public function test_needs_attention_counts_waiting_work_and_links_to_filtered_lists(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        $child = Child::factory()->create();
        $assessment = Assessment::create(['autism_level' => AutismLevelEnum::MILD->value, 'title' => ['en' => 'Screening', 'ar' => 'فحص']]);
        AssessmentSubmission::create(['child_id' => $child->id, 'assessment_id' => $assessment->id, 'status' => 'pending_review']);
        SupportTicket::factory()->create(['status' => SupportTicketStatusEnum::OPEN]);
        $this->createRunningSubscription(expiresAt: '2026-10-23');

        Livewire::test(NeedsAttentionOverview::class)
            ->assertSee(__('Needs attention'))
            ->assertSee(__('Assessments to review'))
            ->assertSee(trans_choice(':count ticket is not assigned to anyone|:count tickets are not assigned to anyone', 1, ['count' => 1]))
            ->assertSee(AssessmentSubmissionResource::getUrl('index', ['tab' => 'waiting']))
            ->assertSee(SupportTicketResource::getUrl('index', ['tab' => 'open']))
            ->assertSee(SubscriptionResource::getUrl('index', ['filters' => ['ending_soon' => ['isActive' => true]]]))
            ->assertSee(ChildResource::getUrl('index', ['filters' => ['has_learning_plan' => ['value' => '0']]]));
    }

    public function test_needs_attention_says_all_clear_when_nothing_is_waiting(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(NeedsAttentionOverview::class)
            ->assertSee(__('All caught up'))
            ->assertSee(__('Every ticket has a specialist'))
            ->assertSee(__('Every child has a plan'));
    }

    public function test_needs_attention_shows_only_the_cards_an_admin_may_open(): void
    {
        $supportAgent = Admin::create([
            'name' => 'Support Agent',
            'email' => 'support@example.com',
            'phone' => '500000002',
            'password' => 'password',
            'is_active' => true,
        ]);
        $supportAgent->givePermissionTo(Permission::findOrCreate('ViewAny:SupportTicket', 'web'));
        $this->actingAs($supportAgent, 'web');

        Livewire::test(NeedsAttentionOverview::class)
            ->assertSee(__('Open support tickets'))
            ->assertDontSee(__('Assessments to review'))
            ->assertDontSee(__('Subscriptions ending soon'))
            ->assertDontSee(__('Children without a learning plan'));
    }

    public function test_range_presets_fill_the_dates(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(Dashboard::class)
            ->call('applyPreset', 'last7Days')
            ->assertSet('filters.startDate', '2026-10-14')
            ->assertSet('filters.endDate', '2026-10-20')
            ->call('applyPreset', 'thisYear')
            ->assertSet('filters.startDate', '2026-01-01');
    }

    public function test_end_date_before_start_date_is_rejected(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(Dashboard::class)
            ->set('filters.startDate', '2026-10-10')
            ->set('filters.endDate', '2026-10-01')
            ->call('applyFilters')
            ->assertHasErrors(['filters.endDate']);
    }

    public function test_excel_export_file_name_carries_the_selected_range(): void
    {
        Excel::fake();
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(Dashboard::class)
            ->call('applyPreset', 'last7Days')
            ->callAction('exportExcel');

        Excel::assertDownloaded('analytics_2026-10-14_to_2026-10-20.xlsx');
    }

    public function test_pdf_export_downloads_with_the_selected_range(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(Dashboard::class)
            ->call('applyPreset', 'last7Days')
            ->callAction('exportPdf')
            ->assertFileDownloaded('analytics_2026-10-14_to_2026-10-20.pdf');
    }

    public function test_pdf_report_is_laid_out_right_to_left_in_an_arabic_font(): void
    {
        app()->setLocale('ar');

        $html = view('pdf.analytics', ['data' => AnalyticsService::getDashboardKpis(now()->subMonth(), now())])->render();

        $this->assertStringContainsString('dir="rtl"', $html);
        // DejaVu Sans ships with mPDF and covers Arabic; a remote font would be fetched on every export.
        $this->assertStringContainsString("font-family: 'dejavusans'", $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString('<h1>تقرير لوحة تحكم مشروع طيف</h1>', $html);
        $this->assertStringContainsString('تم الإنشاء بواسطة طيف', $html);
    }

    public function test_packages_chart_gives_every_package_a_colour(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        foreach (range(1, 11) as $day) {
            $this->createSubscription(100, sprintf('2026-10-%02d', $day), "Package {$day}");
        }

        Livewire::test(SkillMappingPieChart::class, ['pageFilters' => ['startDate' => '2026-10-01', 'endDate' => '2026-10-20']])
            ->assertSee('#64748b');
    }

    private function createRunningSubscription(string $expiresAt): Subscription
    {
        return Subscription::create([
            'parent_id' => User::factory()->create()->id,
            'amount_paid' => 0,
            'status' => SubscriptionStatusEnum::ACTIVE,
            'start_date' => '2026-10-01',
            'expiry_date' => $expiresAt,
            'is_free' => true,
        ]);
    }

    private function createSubscription(float $amountPaid, string $createdAt, string $packageName = 'Monthly'): Subscription
    {
        $package = SubscriptionPackage::create([
            'name' => ['en' => $packageName, 'ar' => $packageName],
            'billing_cycle' => 'monthly',
            'duration_value' => 1,
            'price' => $amountPaid,
        ]);

        $subscription = Subscription::create([
            'parent_id' => User::factory()->create()->id,
            'subscription_package_id' => $package->id,
            'amount_paid' => $amountPaid,
            'status' => SubscriptionStatusEnum::ACTIVE,
            'start_date' => $createdAt,
            'expiry_date' => Carbon::parse($createdAt)->addMonth(),
        ]);

        $subscription->forceFill(['created_at' => $createdAt])->save();

        return $subscription;
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
