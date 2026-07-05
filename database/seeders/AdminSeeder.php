<?php

namespace Database\Seeders;

use App\Models\Admin;
use DB;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
            'password' => Hash::make('12345678'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $role = Role::where('name', 'Super Admin')->first();

        $admin = Admin::where('email', 'admin@app.com')->first();

        $admin->assignRole($role);
    }
}
