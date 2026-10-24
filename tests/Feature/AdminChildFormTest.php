<?php

namespace Tests\Feature;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Enums\SpeechStatusEnum;
use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\Children\Pages\CreateChild;
use App\Filament\Resources\Children\Pages\EditChild;
use App\Models\Admin;
use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminChildFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    public function test_edit_page_shows_the_child_in_the_form(): void
    {
        $child = Child::factory()->create(['name' => 'Salma', 'age' => 7]);

        $this->get(ChildResource::getUrl('edit', ['record' => $child]))
            ->assertOk()
            ->assertSee(__('Child Name'))
            ->assertSee(__('Autism Level'));

        Livewire::test(EditChild::class, ['record' => $child->id])
            ->assertSchemaStateSet([
                'parent_id' => $child->parent_id,
                'name' => 'Salma',
                'age' => 7,
                'gender' => GenderEnum::MALE->value,
                'autism_level' => AutismLevelEnum::MILD->value,
            ]);
    }

    public function test_admin_can_update_a_child(): void
    {
        $child = Child::factory()->create();

        Livewire::test(EditChild::class, ['record' => $child->id])
            ->fillForm([
                'name' => 'Omar',
                'age' => 9,
                'gender' => GenderEnum::FEMALE->value,
                'autism_level' => AutismLevelEnum::SEVERE->value,
                'speech_status' => SpeechStatusEnum::NON_VERBAL->value,
                'educational_status' => 'Grade 3',
                'override_assessment_lock' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $child->refresh();
        $this->assertSame('Omar', $child->name);
        $this->assertSame(9, $child->age);
        $this->assertSame(GenderEnum::FEMALE, $child->gender);
        $this->assertSame(AutismLevelEnum::SEVERE, $child->autism_level);
        $this->assertSame(SpeechStatusEnum::NON_VERBAL, $child->speech_status);
        $this->assertSame('Grade 3', $child->educational_status);
        $this->assertTrue((bool) $child->override_assessment_lock);
    }

    public function test_parent_cannot_be_changed_when_editing(): void
    {
        $child = Child::factory()->create();
        $otherParent = User::factory()->create();

        Livewire::test(EditChild::class, ['record' => $child->id])
            ->assertFormFieldDisabled('parent_id')
            ->fillForm(['parent_id' => $otherParent->id])
            ->call('save');

        $this->assertNotSame($otherParent->id, $child->refresh()->parent_id);
    }

    public function test_edit_rejects_values_the_parent_app_would_reject(): void
    {
        $child = Child::factory()->create();

        Livewire::test(EditChild::class, ['record' => $child->id])
            ->fillForm(['name' => 'A', 'age' => 30, 'gender' => null])
            ->call('save')
            ->assertHasFormErrors(['name' => 'min', 'age' => 'max', 'gender' => 'required']);
    }

    public function test_admin_can_create_a_child_for_a_parent(): void
    {
        $parent = User::factory()->create();

        Livewire::test(CreateChild::class)
            ->fillForm([
                'parent_id' => $parent->id,
                'name' => 'Lina',
                'age' => 5,
                'gender' => GenderEnum::FEMALE->value,
                'autism_level' => AutismLevelEnum::MODERATE->value,
                'speech_status' => SpeechStatusEnum::VERBAL->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Child::class, ['parent_id' => $parent->id, 'name' => 'Lina', 'age' => 5]);
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
