<?php

namespace Tests\Feature;

use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Models\Admin;
use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSliderFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    public function test_first_slide_can_be_created_without_any_data(): void
    {
        Livewire::test(CreateSlider::class)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Slider::class, ['sort_order' => 1, 'is_active' => true]);
    }

    public function test_new_slide_without_an_order_goes_after_the_last_one(): void
    {
        Slider::create(['sort_order' => 4]);

        Livewire::test(CreateSlider::class)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(5, Slider::latest('id')->value('sort_order'));
    }

    public function test_an_order_the_admin_picked_is_kept(): void
    {
        Slider::create(['sort_order' => 4]);

        Livewire::test(CreateSlider::class)
            ->fillForm(['sort_order' => 2, 'is_active' => false])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Slider::class, ['sort_order' => 2, 'is_active' => false]);
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
