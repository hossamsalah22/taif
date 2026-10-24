<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\Admins\Pages\CreateAdmin;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Laravel's plain "email" rule accepts addresses like admin@appcom, so every email field uses email:rfc,filter.
 */
class EmailValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidEmails(): array
    {
        return [
            'domain without a dot' => ['admin@appcom'],
            'single-label domain' => ['a@b'],
            'missing at sign' => ['admin.app.com'],
        ];
    }

    #[DataProvider('invalidEmails')]
    public function test_admin_profile_rejects_an_invalid_email(string $email): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(EditProfile::class)
            ->fillForm(['email' => $email])
            ->call('save')
            ->assertHasFormErrors(['email']);

        $this->assertDatabaseHas(Admin::class, ['email' => 'admin@example.com']);
    }

    public function test_admin_profile_accepts_a_valid_email(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(EditProfile::class)
            ->fillForm(['email' => 'support@taifapp.com'])
            ->call('save')
            ->assertHasNoFormErrors(['email']);

        $this->assertDatabaseHas(Admin::class, ['email' => 'support@taifapp.com']);
    }

    #[DataProvider('invalidEmails')]
    public function test_new_admin_form_rejects_an_invalid_email(string $email): void
    {
        $this->actingAs($this->createSuperAdmin(), 'web');

        Livewire::test(CreateAdmin::class)
            ->fillForm(['email' => $email])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    #[DataProvider('invalidEmails')]
    public function test_parent_app_registration_rejects_an_invalid_email(string $email): void
    {
        $this->postJson('/api/user/register', ['email' => $email])
            ->assertJsonValidationErrors('email');
    }

    public function test_parent_app_registration_accepts_a_valid_email(): void
    {
        $this->postJson('/api/user/register', ['email' => 'parent@example.com'])
            ->assertJsonMissingValidationErrors('email');
    }

    #[DataProvider('invalidEmails')]
    public function test_parent_app_profile_update_rejects_an_invalid_email(string $email): void
    {
        $this->actingAs(User::factory()->create(), 'user')
            ->putJson('/api/user/profile', ['email' => $email])
            ->assertJsonValidationErrors('email');
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
