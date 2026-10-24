<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageGeneral;
use App\Models\Admin;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsContactNumbersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'web');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function saudiMobileFormats(): array
    {
        return [
            'local' => ['0501234567'],
            'without the leading zero' => ['501234567'],
            'international' => ['+966501234567'],
            'international with spaces' => ['+966 50 123 4567'],
            'with 00' => ['00966501234567'],
        ];
    }

    #[DataProvider('saudiMobileFormats')]
    public function test_saudi_numbers_are_accepted_in_any_common_format(string $number): void
    {
        Livewire::test(ManageGeneral::class)
            ->fillForm(['phone' => $number, 'whatsapp' => $number])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(GeneralSettings::class)->refresh();
        $this->assertSame('+966501234567', $settings->phone);
        $this->assertSame('+966501234567', $settings->whatsapp);
    }

    public function test_a_non_saudi_number_explains_the_expected_format(): void
    {
        app()->setLocale('ar');

        Livewire::test(ManageGeneral::class)
            ->fillForm(['phone' => '01001234567', 'whatsapp' => '+201001234567'])
            ->call('save')
            ->assertHasFormErrors(['phone' => 'phone_number', 'whatsapp' => 'phone_number'])
            ->assertSee('أدخل رقمًا سعوديًا، مثل 0501234567');
    }

    public function test_numbers_can_be_left_empty(): void
    {
        Livewire::test(ManageGeneral::class)
            ->fillForm(['phone' => null, 'whatsapp' => null])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_fields_show_an_example_number(): void
    {
        app()->setLocale('ar');

        Livewire::test(ManageGeneral::class)
            ->assertSeeHtml('placeholder="مثال: 0501234567"')
            ->assertSee('رقم سعودي بأي صيغة');
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
