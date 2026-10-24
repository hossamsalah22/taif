<?php

namespace Tests\Feature;

use App\Filament\Resources\Admins\AdminResource;
use App\Filament\Resources\Admins\Schemas\AdminInfolist;
use App\Filament\Resources\Children\ChildResource;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('ar');
        $this->actingAs($this->createAdmin('admin@example.com', 'Super Admin'), 'web');
    }

    public function test_an_admins_page_lists_their_permissions_by_section(): void
    {
        $specialist = $this->createAdmin('specialist@example.com', 'Specialist', ['ViewAny:Child', 'Update:Child', 'Export:Child', 'View:ManageGeneral']);

        $this->get(AdminResource::getUrl('view', ['record' => $specialist]))
            ->assertOk()
            ->assertSee([ChildResource::getPluralModelLabel(), 'عرض الكل', 'تعديل']);

        $rows = collect(AdminInfolist::permissionRows($specialist))->keyBy('section');
        $this->assertSame(['عرض الكل', 'تعديل', 'تصدير'], $rows[ChildResource::getPluralModelLabel()]['actions']);
        $this->assertCount(2, $rows);
    }

    public function test_a_super_admins_page_says_they_can_do_everything(): void
    {
        $this->get(AdminResource::getUrl('view', ['record' => Admin::first()]))
            ->assertOk()
            ->assertSee('يمكن للمشرف العام القيام بكل شيء في اللوحة.');
    }

    public function test_an_admin_without_a_role_has_an_empty_permissions_tab(): void
    {
        $newcomer = $this->createAdmin('new@example.com');

        $this->get(AdminResource::getUrl('view', ['record' => $newcomer]))
            ->assertOk()
            ->assertSee('لا يملك هذا المشرف أي صلاحيات بعد.');
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function createAdmin(string $email, ?string $role = null, array $permissions = []): Admin
    {
        $admin = Admin::create([
            'name' => 'Admin '.$email,
            'email' => $email,
            'phone' => (string) fake()->unique()->numberBetween(500000000, 599999999),
            'password' => 'password',
            'is_active' => true,
        ]);

        if ($role) {
            $admin->assignRole(Role::findOrCreate($role, 'web')->givePermissionTo(
                collect($permissions)->map(fn (string $name) => Permission::findOrCreate($name, 'web')),
            ));
        }

        return $admin;
    }
}
