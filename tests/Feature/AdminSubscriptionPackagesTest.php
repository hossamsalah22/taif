<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatusEnum;
use App\Filament\Resources\SubscriptionPackages\Pages\ListSubscriptionPackages;
use App\Models\Admin;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSubscriptionPackagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    public function test_package_with_active_subscribers_has_no_delete_button(): void
    {
        $subscribed = $this->createPackage();
        $this->subscribe($subscribed, SubscriptionStatusEnum::ACTIVE);
        $unused = $this->createPackage();

        Livewire::test(ListSubscriptionPackages::class)
            ->assertActionHidden(TestAction::make(DeleteAction::class)->table($subscribed))
            ->assertActionVisible(TestAction::make(DeleteAction::class)->table($unused));
    }

    public function test_bulk_delete_skips_packages_with_active_subscribers(): void
    {
        $subscribed = $this->createPackage();
        $this->subscribe($subscribed, SubscriptionStatusEnum::ACTIVE);
        $expiredOnly = $this->createPackage();
        $this->subscribe($expiredOnly, SubscriptionStatusEnum::EXPIRED);
        $unused = $this->createPackage();

        Livewire::test(ListSubscriptionPackages::class)
            ->selectTableRecords([$subscribed, $expiredOnly, $unused])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

        $this->assertModelExists($subscribed);
        $this->assertModelMissing($expiredOnly);
        $this->assertModelMissing($unused);
    }

    private function createPackage(): SubscriptionPackage
    {
        return SubscriptionPackage::create([
            'name' => ['en' => 'Monthly', 'ar' => 'شهري'],
            'billing_cycle' => 'monthly',
            'duration_value' => 1,
            'price' => 100,
        ]);
    }

    private function subscribe(SubscriptionPackage $package, SubscriptionStatusEnum $status): Subscription
    {
        return Subscription::create([
            'parent_id' => User::factory()->create()->id,
            'subscription_package_id' => $package->id,
            'amount_paid' => 100,
            'status' => $status,
            'start_date' => now()->subMonth(),
            'expiry_date' => now()->addMonth(),
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
