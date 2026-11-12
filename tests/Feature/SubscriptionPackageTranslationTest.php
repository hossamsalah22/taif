<?php

namespace Tests\Feature;

use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPackageTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_details_are_shown_in_english(): void
    {
        $this->createPackage();

        $this->actingAsParent('en')
            ->getJson('/api/user/subscriptions/gateway')
            ->assertOk()
            ->assertJsonPath('data.packages.0.name', 'Monthly plan')
            ->assertJsonPath('data.packages.0.billing_cycle_label', 'Monthly')
            ->assertJsonPath('data.packages.0.features', [
                ['feature' => 'Weekly sessions'],
                ['feature' => 'Progress reports'],
            ]);
    }

    public function test_package_details_are_shown_in_arabic(): void
    {
        $this->createPackage();

        $this->actingAsParent('ar')
            ->getJson('/api/user/subscriptions/gateway')
            ->assertOk()
            ->assertJsonPath('data.packages.0.name', 'الباقة الشهرية')
            ->assertJsonPath('data.packages.0.features', [
                ['feature' => 'جلسات أسبوعية'],
                ['feature' => 'تقارير التقدم'],
            ]);
    }

    public function test_a_feature_missing_in_the_requested_language_falls_back_to_the_other_one(): void
    {
        SubscriptionPackage::factory()->create([
            'features' => [
                ['feature' => ['ar' => 'دعم فني', 'en' => null]],
                ['feature' => 'Plain text feature'],
            ],
        ]);

        $this->actingAsParent('en')
            ->getJson('/api/user/subscriptions/gateway')
            ->assertOk()
            ->assertJsonPath('data.packages.0.features', [
                ['feature' => 'دعم فني'],
                ['feature' => 'Plain text feature'],
            ]);
    }

    /**
     * The gateway reads the parent from the sanctum guard, while the route is protected by the user guard.
     */
    private function actingAsParent(string $locale): static
    {
        $parent = User::factory()->create(['locale' => $locale]);

        return $this->actingAs($parent, 'sanctum')->actingAs($parent, 'user');
    }

    private function createPackage(): SubscriptionPackage
    {
        return SubscriptionPackage::factory()->create([
            'features' => [
                ['feature' => ['ar' => 'جلسات أسبوعية', 'en' => 'Weekly sessions']],
                ['feature' => ['ar' => 'تقارير التقدم', 'en' => 'Progress reports']],
            ],
        ]);
    }
}
