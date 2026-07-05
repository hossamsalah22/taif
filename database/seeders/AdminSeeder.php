<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Country;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->insert([
            'name' => 'Owner',
            'email' => 'admin@app.com',
            'phone' => '+966512345678',
            'password' => Hash::make('12345678'),
            'country_code' => Country::where('iso', 'SA')->first()->iso,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $role = Role::where('name', 'Super Admin')->first();

        $admin = Admin::where('email', 'admin@app.com')->first();

        $admin->assignRole($role);
    }
}
