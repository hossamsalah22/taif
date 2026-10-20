<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatusEnum;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManageGeneral;
use App\Filament\Resources\SubscriptionPackages\Pages\CreateSubscriptionPackage;
use App\Filament\Widgets\DashboardKpiOverview;
use App\Filament\Widgets\PerformanceLineChart;
use App\Filament\Widgets\RevenueBreakdownOverview;
use App\Filament\Widgets\SkillMappingPieChart;
use App\Models\Admin;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
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

    private function createSubscription(float $amountPaid, string $createdAt): Subscription
    {
        $package = SubscriptionPackage::create([
            'name' => ['en' => 'Monthly', 'ar' => 'شهري'],
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
