<?php

namespace Tests\Feature;

use App\Enums\RewardTypeEnum;
use App\Filament\Resources\Rewards\Pages\CreateReward;
use App\Models\Admin;
use Filament\Forms\Components\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRewardFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_sound_reward_asks_for_a_sound_file_in_arabic(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');
        app()->setLocale('ar');

        Livewire::test(CreateReward::class)
            ->fillForm(['type' => RewardTypeEnum::SOUND->value])
            ->assertFormFieldVisible('sound')
            ->assertFormFieldHidden('image')
            ->assertFormFieldExists('sound', fn (Field $field): bool => $field->getLabel() === 'الملف الصوتي')
            ->assertDontSee('Sound');
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
